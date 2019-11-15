<?php

namespace Aeria\Updater;

/**
 * The Updater is in charge of checking updates on the GitHub repo.
 *
 * @category Updater
 *
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class Updater
{
    /**
     * Constructs the Updater object.
     *
     *
     * @since  Method available since Release 3.0.0
     */
    public function __construct()
    {
        // define the alternative API for updating checking
        add_filter('pre_set_site_transient_update_plugins', array($this, 'checkVersion'));
        // Define the alternative response for information checking
        add_filter('plugins_api', array($this, 'setPluginInfo'), 10, 3);
        // reactivate plugin
        add_filter('upgrader_post_install', array($this, 'postInstall'), 10, 3);
    }

    /**
     * Saves the provided config.
     *
     * @param array $config the configuration
     *
     * @since  Method available since Release 3.0.0
     */
    public function config($config)
    {
        $this->config = $config;
    }

    /**
     * Gets the plugin data from plugin.php.
     *
     * @return mixed the plugin data
     *
     * @since  Method available since Release 3.0.0
     */
    private function getPluginData()
    {
        if (isset($this->pluginData)) {
            return $this->pluginData;
        }

        include_once ABSPATH.'/wp-admin/includes/plugin.php';

        $this->pluginData = get_plugin_data(WP_PLUGIN_DIR.'/'.$this->config['slug']);

        $githubUrl = parse_url($this->pluginData['PluginURI']);
        $githubInfo = explode('/', $githubUrl['path']);

        $this->github = [
          'user' => $githubInfo[1],
          'repository' => $githubInfo[2],
        ];
    }

    /**
     * Gets the release infos from the GitHub repo.
     *
     * @return mixed|void a transient or nothing
     *
     * @since  Method available since Release 3.0.0
     */
    private function getRepoReleaseInfo()
    {
        //Only do this once
        if (!empty($this->githubAPIResult)) {
            return;
        }

        $transient = get_transient("{$this->github['user']}_{$this->github['repository']}_transient_update");
        if ($transient !== false) {
            $this->githubAPIResult = $transient;

            return;
        }
        // Query the GitHub API
        $url = "https://api.github.com/repos/{$this->github['user']}/{$this->github['repository']}/releases";
        // We need the access token for private repos
        if (!empty($this->config['access_token'])) {
            $url = add_query_arg(array('access_token' => $this->config['access_token']), $url);
        }

        // Get the results
        $this->githubAPIResult = wp_remote_retrieve_body(wp_remote_get($url));
        if (!empty($this->githubAPIResult)) {
            $this->githubAPIResult = @json_decode($this->githubAPIResult);
        }
        // Use only the latest release
        if (is_array($this->githubAPIResult)) {
            $this->githubAPIResult = $this->githubAPIResult[0];
        }
        set_transient("{$this->github['user']}_{$this->github['repository']}_transient_update", $this->githubAPIResult, 3.600);
    }

    /**
     * Compares the current version to the installed one.
     *
     * @param mixed $transient the updates transient
     *
     * @return mixed the transient
     *
     * @since  Method available since Release 3.0.0
     */
    public function checkVersion($transient)
    {
        if (!empty($transient) && empty($transient->checked)) {
            return $transient;
        }

        // Get plugin & GitHub release information
        $this->getPluginData();
        $this->getRepoReleaseInfo();

        if (!isset($this->githubAPIResult->tag_name)) {
            return $transient;
        }
        $doUpdate = version_compare($this->githubAPIResult->tag_name, $transient->checked[$this->config['slug']]);

        if ($doUpdate == 1) {
            $package = $this->githubAPIResult->zipball_url;
            // Include the access token for private GitHub repos
            if (!empty($this->config['access_token'])) {
                $package = add_query_arg(array('access_token' => $this->config['access_token']), $package);
            }

            $obj = new \StdClass();
            $obj->slug = $this->config['slug'];
            $obj->new_version = $this->githubAPIResult->tag_name;
            $obj->url = $this->pluginData['PluginURI'];
            $obj->package = $package;
            $obj->plugin = $this->config['slug'];
            $obj->icons = ['1x' => '/wp-content/plugins/aeria/Aeria/aeria-transparent.png'];
            $transient->response[$this->config['slug']] = $obj;
        }

        return $transient;
    }

    /**
     * Sets the plugin infos.
     *
     * @param mixed $false    unused parameter sent by the filter
     * @param mixed $action   unused parameter sent by the filter
     * @param mixed $response the updates check response
     *
     * @return mixed the response for the transient
     *
     * @since  Method available since Release 3.0.0
     */
    public function setPluginInfo($false, $action, $response)
    {
        // Get plugin & GitHub release information
        $this->getPluginData();
        $this->getRepoReleaseInfo();

        // If nothing is found, do nothing
        if (empty($response->slug) || $response->slug != $this->config['slug']) {
            return false;
        }
        // Add our plugin information
        $response->last_updated = $this->githubAPIResult->published_at;
        $response->slug = $this->config['slug'];
        $response->name = $this->pluginData['Name'];
        $response->plugin_name = $this->pluginData['Name'];
        $response->version = $this->githubAPIResult->tag_name;
        $response->author = $this->pluginData['AuthorName'];
        $response->homepage = $this->pluginData['PluginURI'];

        $response->sections = array('description' => $this->githubAPIResult->body);
        // This is our release download zip file
        $downloadLink = $this->githubAPIResult->zipball_url;
        // Include the access token for private GitHub repos
        if (!empty($this->config['access_token'])) {
            $downloadLink = add_query_arg(
                array('access_token' => $this->config['access_token']),
                $downloadLink
            );
        }
        $response->download_link = $downloadLink;

        return $response;
    }

    /**
     * Reactivates the plugin after installation.
     *
     * @param mixed $true       unused parameter sent by the filter
     * @param mixed $hook_extra unused parameter sent by the filter
     * @param mixed $result     the result of the update
     *
     * @return mixed the response for the transient
     *
     * @since  Method available since Release 3.0.0
     */
    public function postInstall($true, $hook_extra, $result)
    {
        global $wp_filesystem;
        // Move & Activate
        $proper_destination = WP_PLUGIN_DIR.'/'.$this->config['proper_folder_name'];
        $wp_filesystem->move($result['destination'], $proper_destination);
        $result['destination'] = $proper_destination;
        $activate = activate_plugin(WP_PLUGIN_DIR.'/'.$this->config['slug']);
        // Output the update message
        $fail = __('The plugin has been updated, but could not be reactivated. Please reactivate it manually.', 'github_plugin_updater');
        $success = __('Plugin reactivated successfully.', 'github_plugin_updater');
        echo is_wp_error($activate) ? $fail : $success;

        return $result;
    }
}
