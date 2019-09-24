<?php

namespace Aeria\OptionsPage;

use Aeria\OptionsPage\OptionsPageProcessor;
use Aeria\Field\FieldError;
use Aeria\RenderEngine\RenderEngine;
/**
 * OptionsPage is in charge of generating pages in WP's options 
 *  
 * @category Options
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class OptionsPage
{

    private $option_pages = [];
    /**
     * Returns the required fields to get a WP nonce
     *
     * @return array the nonce fields
     *
     * @access private
     * @static
     * @since  Method available since Release 3.0.0
     */
    private static function nonceIDs()
    {
        return [
          'action' => 'update-',
          'field' => 'update_aeria_settings'
        ];
    }
    /**
     * Registers an options page
     *
     * @param array $option_page the options page configuration
     *
     * @return null
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function register($option_page)
    {
        // Check if all the required fields are available
        if(isset($option_page["title"])
            &&isset($option_page["menu_title"])
            &&isset($option_page["capability"])
            &&isset($option_page["menu_slug"])
            &&isset($option_page["config"])
            &&isset($option_page["sections"])
            &&isset($option_page["validator_service"])
            &&isset($option_page["query_service"])
        )
            $this->optionPages[]=$option_page;
            return null;
    }
    /**
     * Registers the saved options pages to WP
     *
     * @param array        $aeria_config    the full configuration for Aeria
     * @param RenderEngine $render_service the service in charge of rendering HTML
     *
     * @return array the nonce fields
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function boot($aeria_config, $render_service)
    {
        $default_icon_data = file_get_contents(dirname(__DIR__).'/aeria.svg');
        $default_icon = 'data:image/svg+xml;base64,'.base64_encode($default_icon_data);
        // Registering aeria's editor page
        if (empty($GLOBALS['admin_page_hooks']["aeria_options"])) {
            add_menu_page(
                "Aeria editor",
                "Aeria",
                "manage_options",
                "aeria_options",
                '',
                $default_icon
            );        
        }
        add_submenu_page(
            "aeria_options",
            "Editor",
            "Editor",
            "manage_options",
            "aeria_options",
            function () use ($aeria_config, $render_service) {
                static::renderHTML(
                    "aeria_editor",
                    $aeria_config,
                    $render_service
                );
            }
        );
        // Registering other option pages
        foreach ($this->optionPages as $singleOptionPage) {
        // Check if nav menu parent exists
            if (empty($GLOBALS['admin_page_hooks'][$singleOptionPage["parent"]])) {
                add_menu_page(
                    $singleOptionPage["title"],
                    $singleOptionPage["parent_title"],
                    $singleOptionPage["capability"],
                    $singleOptionPage["parent"],
                    '',
                    $singleOptionPage["parent_icon"]
                );
                $singleOptionPage["menu_slug"] = $singleOptionPage["parent"];
            }
            add_submenu_page(
                $singleOptionPage["parent"],
                $singleOptionPage["title"],
                $singleOptionPage["menu_title"],
                $singleOptionPage["capability"],
                $singleOptionPage["menu_slug"],
                function () use ($singleOptionPage, $render_service) {
                    // If we're saving the fields, POST will be instantiated
                    if (isset($_POST)) {
                        static::save(
                            $singleOptionPage["menu_slug"],
                            $singleOptionPage["config"],
                            $singleOptionPage["validator_service"],
                            $singleOptionPage["query_service"],
                            $singleOptionPage["sections"],
                            $render_service
                        );
                    }
                    static::renderHTML(
                        $singleOptionPage["menu_slug"],
                        $singleOptionPage["config"],
                        $render_service,
                        $singleOptionPage["validator_service"],
                        $singleOptionPage["query_service"],
                        $singleOptionPage["sections"]
                    );
                }
            );
        }
    }
    /**
     * Calls the render service to render HTML
     *
     * @param string       $id                the option page ID
     * @param array        $config             the options page configuration
     * @param RenderEngine $render_service    the service in charge of rendering HTML
     * @param Validator    $validator_service the validation service for the fields
     * @param Query        $query_service     the required query service
     * @param array        $sections          the sections configuration
     *
     * @return void
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function renderHTML($id, $config, $render_service, $validator_service = null,  $query_service = null, $sections = null)
    {
        if ($id == "aeria_editor") {
            $render_service->render(
                'editor_template',
                [
                    "config" => $config,
                ]
            );
            return null;
        }
        $nonceIDs = static::nonceIDs();
        $processor = new OptionsPageProcessor($id, $config, $sections, $render_service);
        $config["fields"] = $processor->getAdmin();
                $render_service->render(
                    'option_template',
                    [
                        "config" => $config,
                        "nonceIDs" => $nonceIDs
                    ]
                );
    }

    /**
     * Saves the new data to WP
     *
     * @param string    $id                the options page ID
     * @param array     $metabox           the metabox configuration
     * @param Validator $validator_service the validation service for fields
     * @param Query     $query_service     the required query service
     * @param array     $sections          the sections configuration
     *
     * @return void
     * @throws ConfigValidationException in case of an invalid config
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function save($id, $metabox, $validator_service, $query_service, $sections, $render_service)
    {
        if ($_POST==[]) {
            return null;
        }
        $nonceIDs = static::nonceIDs();

            if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                || !check_admin_referer($nonceIDs['action'], $nonceIDs['field'])
                || !current_user_can("manage_options"))
            {
                return null;
            }
            $processor = new OptionsPageProcessor($id, $metabox, $sections, $render_service, $_POST);
            $processor->set(
                $validator_service,
                $query_service
            );
    }
}
