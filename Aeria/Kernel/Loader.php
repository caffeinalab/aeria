<?php

namespace Aeria\Kernel;

use Aeria\Config\Config;
use Aeria\Container\Container;
use Aeria\RenderEngine\ViewFactory;

/**
 * Loader is the class in charge of fetching configuration files from your theme.
 *
 * @category Config
 *
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class Loader
{
    /**
     * This method loads a config.
     *
     * @param Config    $config    the config to be loaded
     * @param Container $container our container
     *
     * @since  Method available since Release 3.0.0
     */
    public static function loadConfig(Config $config, Container $container)
    {
        $root_paths = $config->getRootPath();
        $render_service = $container->make('render_engine');
        foreach ($root_paths as $root_path) {
            static::recursiveLoad($config, $root_path, $render_service);
        }
    }

    /**
     * This method recursively loads the views for the renderer.
     *
     * @param string $base_path      the base path of the view
     * @param Render $render_service the render service to load the views to
     * @param string $context        the context of the file
     *
     * @since  Method available since Release 3.0.0
     */
    public static function loadViews($base_path, $render_service, $context = '')
    {
        $new_base_path = static::joinPaths($base_path, $context);
        if (!is_dir('/'.$new_base_path)) {
            return null;
        }
        $listDir = array_filter(
            scandir('/'.$new_base_path),
            function ($path) { return $path !== '.' && $path !== '..'; }
        );

        foreach ($listDir as $dir_or_file_name) {
            $absolute_path = '/'.static::joinPaths(
                $new_base_path,
                $dir_or_file_name
            );
            if (is_dir($absolute_path)) {
                static::loadViews($new_base_path, $render_service, $dir_or_file_name);
            } else {
                $render_service->register(ViewFactory::make($absolute_path));
            }
        }
    }

    /**
     * This method recursively loads the configs.
     *
     * @param Config $config         the config to be passed to loadSettings
     * @param string $base_path      the base path of the view
     * @param Render $render_service the render service to load the views to
     * @param string $context        the context of the file
     *
     * @since  Method available since Release 3.0.0
     */
    public static function recursiveLoad(
        Config $config,
        string $base_path,
        $render_service,
        string $context = ''
    ) {
        $permitted_file_extensions = ['json', 'php', 'yml', 'ini'];
        $new_base_path = static::joinPaths($base_path, $context);
        if (!is_dir('/'.$new_base_path)) {
            return null;
        }
        $listDir = array_filter(
            scandir('/'.$new_base_path),
            function ($path) { return $path !== '.' && $path !== '..'; }
        );
        foreach ($listDir as $dir_or_file_name) {
            $absolute_path = '/'.static::joinPaths(
                $new_base_path,
                $dir_or_file_name
            );
            if (is_dir($absolute_path)) {
                static::recursiveLoad($config, $new_base_path, $render_service, $dir_or_file_name);
            } elseif (in_array(substr($dir_or_file_name, strrpos($dir_or_file_name, '.') + 1), $permitted_file_extensions)) {
                try {
                    static::loadSettings(
                        $config,
                        $absolute_path,
                        $context
                    );
                } catch (\Exception $e) {
                    add_action(
                        'admin_notices',
                        function () use ($absolute_path, $render_service, $e) {
                            $render_service->render(
                                'admin_notice_template',
                                ['type' => 'error',
                                'dismissible' => false,
                                'message' => 'A wrong configuration file was found in '.$absolute_path.' - '.$e->getMessage(), ]
                            );
                        }
                    );
                }
            } else {
                add_action(
                        'admin_notices',
                        function () use ($absolute_path, $render_service) {
                            $render_service->render(
                                'admin_notice_template',
                                ['type' => 'warning',
                                'dismissible' => true,
                                'message' => 'You inserted a file with an unsupported extension in the config folder: '.$absolute_path, ]
                            );
                        }
                    );
            }
        }
    }

    /**
     * This method loads settings from a config.
     *
     * @param Config $config    the fetched config
     * @param string $file_path the file path
     * @param string $context   the context of the file
     *
     * @since  Method available since Release 3.0.0
     */
    private static function loadSettings(Config $config, string $file_path, string $context)
    {
        $parsed_file = $config->
            { Config::getParserMethod($config->getDriverInUse($file_path)) }($file_path);

        $config->isValidStandardConfiguration($parsed_file);

        $name = $parsed_file['name'];
        $spec_list = $parsed_file['spec'];
        $kind = $parsed_file['kind'];
        $enabled = isset($parsed_file['enabled']) ? $parsed_file['enabled'] : true;
        $namespace = $kind == 'controller' || $kind == 'route' ?
          "global.{$kind}.{$name}" :
          "aeria.{$kind}.{$name}";
        if ($enabled) {
            $config->
                { Config::getLoaderMethod($config->getDriverInUse($file_path)) }(
                    $spec_list,
                    $namespace
                );
        }
    }

    /**
     * This method joins paths.
     *
     * @return string joined paths
     *
     * @static
     *
     * @since  Method available since Release 3.0.0
     */
    private static function joinPaths()
    {
        $args = func_get_args();
        $paths = array();
        foreach ($args as $arg) {
            $paths = array_merge($paths, (array) $arg);
        }

        $paths = array_map(function ($p) { return trim($p, '/'); }, $paths);
        $paths = array_filter($paths);

        return join('/', $paths);
    }
}
