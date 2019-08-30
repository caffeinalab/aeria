<?php

namespace Aeria\Kernel;

use Aeria\Config\Config;
use Aeria\Container\Container;
use Aeria\PostType\PostType;
use Aeria\Meta\Meta;
use Aeria\OptionsPage\OptionsPage;
use Aeria\Taxonomy\Taxonomy;
use Aeria\Validator\Validator;
use Aeria\Router\Router;
use Aeria\Router\ControllerRegister;
use Aeria\Query\Query;
use Aeria\Updater\Updater;
use Aeria\Router\Route;
use Aeria\Router\Factory\RouteFactory;
use Aeria\RenderEngine\ViewFactory;
use Aeria\Config\Exceptions\DecodeException;

class Kernel
{
    protected $driver_name = '';

    public function driverType(string $name)
    {
        $this->driver_name = $name;
    }

    public function loadConfig(Config $config)
    {
        $root_path = $config->getRootPath();
        $this->recursiveLoad($config, $root_path);
    }


    public function loadViews($base_path, $render_service, $context = '')
    {
        $new_base_path = Kernel::joinPaths($base_path, $context);
        if (!is_dir('/'.$new_base_path))
            return null;
        $listDir = array_filter(
            scandir('/' . $new_base_path),
            function ($path) { return $path !== '.' && $path !== '..'; }
        );
        foreach ($listDir as $dir_or_file_name) {
            $absolute_path = '/' . Kernel::joinPaths(
                $new_base_path,
                $dir_or_file_name
            );
            if (is_dir($absolute_path)) {
                $this->loadViews($new_base_path, $render_service, $dir_or_file_name);
            } else {
                $render_service->register(ViewFactory::make($absolute_path));
            }
        }
    }

    public function recursiveLoad(
        Config $config,
        string $base_path,
        string $context = ''
    ) {
        $permitted_file_extensions = ['json', 'php', 'yml', 'ini'];
        $new_base_path = Kernel::joinPaths($base_path, $context);
        if (!is_dir('/'.$new_base_path))
            return null;
        $listDir = array_filter(
            scandir('/' . $new_base_path),
            function ($path) { return $path !== '.' && $path !== '..'; }
        );
        foreach ($listDir as $dir_or_file_name) {
            $absolute_path = '/' . Kernel::joinPaths(
                $new_base_path,
                $dir_or_file_name
            );
            if (is_dir($absolute_path)) {
                $this->recursiveLoad($config, $new_base_path, $dir_or_file_name);
            } else if (in_array(substr($dir_or_file_name, strrpos($dir_or_file_name, '.')+1), $permitted_file_extensions)){
                try{
                $this->loadSettings(
                    $config,
                    $absolute_path,
                    $context
                );
                }catch (\Exception $e){
                    add_action( 'admin_notices', function () use ($absolute_path) {
                        ?>
                        <div class="notice notice-error">
                            <p><strong>Aeria:</strong> A wrong configuration file was found in <?=$absolute_path?></p>
                        </div>
                        <?php
                    });
                }
            } else {
                add_action( 'admin_notices', function () use ($absolute_path) {
                    ?> 
                    <div class="notice notice-warning is-dismissible">
                        <p><strong>Aeria:</strong> You inserted a file with an unsupported extension in the config folder:  <?=$absolute_path?></p>
                    </div>
                    <?php
                });
            }
        }
    }

    private function loadSettings(Config $config, string $file_path, string $context)
    {
        $parsed_file = $config->
            { Config::getParserMethod($config->getDriverInUse($file_path)) }($file_path);

        $config->isValidStandardConfiguration($parsed_file);

        $name = $parsed_file['name'];
        $spec_list = $parsed_file['spec'];
        $kind = $parsed_file['kind'];
        $namespace = $kind == 'controller' || $kind == 'route' ?
          "global.{$kind}.{$name}" :
          "aeria.{$kind}.{$name}";

        $config->
            { Config::getLoaderMethod($config->getDriverInUse($file_path)) }(
                $spec_list,
                $namespace
            );
    }

    private static function joinPaths()
    {
        $args = func_get_args();
        $paths = array();
        foreach ($args as $arg) {
            $paths = array_merge($paths, (array)$arg);
        }

        $paths = array_map(function ($p) { return trim($p, "/"); }, $paths);
        $paths = array_filter($paths);
        return join('/', $paths);
    }

    public function createMeta(Container $container)
    {
        $meta_service = $container->make('meta');
        $metas = $container->make('config')->get('aeria.meta', []);
        $sections = $container->make('config')->get('aeria.section', []);
        $validator_service=$container->make('validator');
        $query_service=$container->make('query');
        $render_service = $container->make('render_engine');
        foreach ($metas as $name => $data) {
            $meta_config = array_merge(
                ['id' => $name],
                $data
            );
            add_action(
                'add_meta_boxes',
                function () use ($meta_config, $meta_service, $sections, $render_service) {
                    $meta = $meta_service->create($meta_config, $sections, $render_service);
                }
            );
            add_action('save_post', Meta::save($meta_config, $_POST, $validator_service, $query_service, $sections), 10, 2);
        }

        add_action( 'admin_print_scripts', function () use ($sections) {
          ?>
          <script>
            window.aeriaSections = <?=wp_json_encode($sections);?>;
          </script>
          <?php
        });

        add_action('admin_head', function(){
          global $_wp_admin_css_colors;
          $admin_colors = $_wp_admin_css_colors;
          $aeria_colors = $admin_colors[get_user_option('admin_color')]->colors;
          ?>
            <script>
              window.aeriaTheme = <?=wp_json_encode($aeria_colors);?>;
            </script>
          <?php
        });
    }

    public function getMeta(Container $container)
    {
    }

    public function createPostType(Container $container)
    {
        $post_type_service = $container->make('post_type');
        $post_types = $container->make('config')->get('aeria.post-type', []);
        foreach ($post_types as $name => $data) {
            $post_type = $post_type_service->create(
                array_merge(
                    ['post_type' => $name],
                    $data
                )
            );
        }
    }

    public function createRouter(Container $container)
    {
        $router_service = $container->make('router');
        $validator_service = $container->make('validator');
        $query_service = $container->make('query');
        $routes = $container->make('config')->get('global.route');
        $metaboxes = $container->make('config')->get('aeria.meta');

        $router_service->get(
            "/validate", function ($request) use ($validator_service) {
                $wp_req = $request->wp_request;
                return $validator_service->validate($wp_req["field"], $wp_req["validators"]);
            }
        );
        $router_service->get(
            "/search", function ($request) use ($query_service) {
                $wp_req = $request->wp_request;
                return $query_service->getPosts($wp_req->get_params());
            }
        );
        $router_service->get(
            "/post-types", function ($request) use ($query_service) {
                $wp_req = $request->wp_request;
                return $query_service->getPostTypes($wp_req->get_params());
            }
        );
        $router_service->get(
            "/taxonomies", function ($request) use ($query_service) {
                $wp_req = $request->wp_request;
                return $query_service->getTaxonomies($wp_req->get_params());
            }
        );
        $router_service->get(
            "/validate-by-id", function ($request) use ($validator_service, $metaboxes) {
                $wp_req = $request->wp_request;
                return $validator_service->validateByID($wp_req["field_id"], $wp_req["value"], $metaboxes);
            }
        );

        if (is_array($routes)){
            $routes_config = array_flat($routes, 1);

            foreach ($routes_config as $config) {
                $route = RouteFactory::make($config);
                $router_service->register($route);
            }
        }

        $router_service->boot();
    }

    public function createControllers(Container $container)
    {
        $router_service = $container->make('router');
        $controllers_service = $container->make('controller');
        $controllers = $container->make('config')->get('global.controller');
        if (!is_null($controllers)){
            foreach ($controllers as $name => $controller_config) {
                $controllers_service->register($controller_config['namespace']);
            }
        }
    }

    public function createTaxonomy(Container $container)
    {
        $taxonomy_service = $container->make('taxonomy');
        $taxonomies = $container->make('config')->get('aeria.taxonomy', []);
        foreach ($taxonomies as $name => $data) {
            $taxonomy = $taxonomy_service->create(
                array_merge(
                    ['taxonomy' => $name],
                    $data
                )
            );
        }
    }

    public function createValidator (Container $container)
    {
        $validator_service = $container->make('validator');
    }

    public function createQuery (Container $container)
    {
        $query_service = $container->make('query');
    }

    public function createUpdater (Container $container)
    {
      $updater_service = $container->make('updater');
      $updater_service->config([
          // "access_token" => "",
          "slug" => 'aeria/aeria.php',
          "version" => $container->version(),
          "proper_folder_name" => 'aeria'
      ]);

    }

    public function createRenderer(Container $container)
    {
        $render_service = $container->make('render_engine');
        $root_paths = $render_service->getRootPaths();
        foreach ($root_paths as $root_path) {
            $this->loadViews($root_path, $render_service);
        }
    }

    public function createField(Container $container)
    {
        $field_service = $container->make('field');

        $field_service->register('base', \Aeria\Field\Fields\BaseField::class);
        $field_service->register('repeater', \Aeria\Field\Fields\RepeaterField::class);
        $field_service->register('gallery', \Aeria\Field\Fields\GalleryField::class);
        $field_service->register('picture', \Aeria\Field\Fields\PictureField::class);
        $field_service->register('sections', \Aeria\Field\Fields\SectionsField::class);
        $field_service->register('select', \Aeria\Field\Fields\SelectField::class);
        $field_service->register('switch', \Aeria\Field\Fields\SwitchField::class);
        $field_service->register('relation', \Aeria\Field\Fields\RelationField::class);

        // example of multiple registered fields with the same handler; not
        // really needed in this case, as they use the default BaseField, but
        // colud be useful; `register` accepts a third value: `override`.
        // So, having the list of overridable fields here it's not a bad idea.
        $field_service->register('text', \Aeria\Field\Fields\BaseField::class);
        $field_service->register('textarea', \Aeria\Field\Fields\BaseField::class);
        $field_service->register('wysiwyg', \Aeria\Field\Fields\BaseField::class);
        $field_service->register('number', \Aeria\Field\Fields\BaseField::class);
        $field_service->register('email', \Aeria\Field\Fields\BaseField::class);
        $field_service->register('url', \Aeria\Field\Fields\BaseField::class);

        do_action('aeria_register_field', $field_service, $container);
    }

    public function createOptionsPage(Container $container)
    {
        $options_service = $container->make('options');
        $options = $container->make('config')->get('aeria.options', []);
        $sections = $container->make('config')->get('aeria.section', []);
        $validator_service=$container->make('validator');
        $query_service=$container->make('query');
        $render_service = $container->make('render_engine');
        $aeriaConfig = $container->make('config')->all();
        $default_icon_data = file_get_contents(dirname(__DIR__).'/aeria.svg');
        $default_icon = 'data:image/svg+xml;base64,'.base64_encode($default_icon_data);
        // Registering other pages
        foreach ($options as $name => $data) {
            $option_config =[];
            $option_config = array_merge(
                ['id' => $name],
                $data
            );

            $theOptionPage = [
                "title" => $option_config["title"],
                "menu_title" => $option_config["title"],
                "capability" => isset($option_config["capability"]) ? $option_config["capability"] : "manage_options",
                "menu_slug" => $option_config["menu_slug"],
                "parent" => isset($option_config["parent"]) ? $option_config["parent"] : "options-general.php",
                "parent_title" => isset($option_config["parent_title"]) ? $option_config["parent_title"] : "",
                "parent_icon" => isset($option_config["parent_icon"]) ? $option_config["parent_icon"] : $default_icon,
                "config" => $option_config,
                "sections" => $sections,
                "validator_service" => $validator_service,
                "query_service" => $query_service
            ];
            $options_service->register($theOptionPage);
        }
        add_action(
            'admin_menu',
            function () use ($options_service, $aeriaConfig, $render_service) {
                $options_service->boot($aeriaConfig, $render_service);
            }
        );
    }


}
