<?php

namespace Aeria\Config;

use Aeria\Config\Interfaces\DriverInterface;
use Aeria\Container\Interfaces\ExtensibleInterface;
use Aeria\Config\Traits\ValidateConfTrait;
use Aeria\Container\Interfaces\ValidateConfInterface;
use JsonSerializable;
use Aeria\Structure\Traits\ExtensibleTrait;
use Aeria\Structure\Traits\DictionaryTrait;
use Aeria\Config\Exceptions\DriverException;
use Aeria\Config\Exceptions\InvalidNamespaceException;

/**
 * Config is the class in charge of parsing configuration files from your theme.
 *
 * @category Config
 *
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class Config implements ExtensibleInterface, JsonSerializable, ValidateConfInterface
{
    use ExtensibleTrait;
    use DictionaryTrait {
        DictionaryTrait::__construct as instanciateDictionary;
    }
    use ValidateConfTrait;

    protected $drivers = [];
    protected $root_paths = [];
    protected $active_driver = 'json';
    private $_kind;

    /**
     * Constructs the Config singleton.
     *
     * We're adding the theme folder aeria-config, and the folder in resources.
     * Both contain configuration files for Aeria.
     *
     * @since  Method available since Release 3.0.0
     */
    public function __construct()
    {
        $template_dir = get_template_directory();
        $stylesheet_dir = get_stylesheet_directory();

        $this->instanciateDictionary();
        $this->root_paths[] = $template_dir.'/aeria-config';

        // extending support to child themes
        if ($stylesheet_dir !== $template_dir) {
            $this->root_paths[] = $stylesheet_dir.'/aeria-config';
        }

        $this->root_paths[] = WP_PLUGIN_DIR.'/aeria/resources/Config';
        $custom_paths = [];
        $custom_paths = apply_filters('aeria_register_configs', $custom_paths);

        foreach ($custom_paths as $path) {
            $this->setRootPath($path);
        }
    }

    /**
     * Returns the validation array.
     *
     * The returned array contains the validators we need in the config.
     * It is structured as the config files.
     *
     * @return array the validators array
     *
     * @since  Method available since Release 3.0.0
     */
    public function getValidationStructure(): array
    {
        switch ($this->_kind) {
        case 'meta':
            $spec = [
                'title' => $this->makeRegExValidator(
                    '/^.{1,100}$/'
                ),
                'context' => $this->makeRegExValidator(
                    '/^normal|side|advanced$/'
                ),
                'post_type' => function ($value) {
                    return [
                        'result' => is_array($value),
                        'message' => 'post_type should be an array',
                    ];
                },
                'fields' => function ($value) {
                    return [
                        'result' => is_array($value),
                        'message' => 'fields should be an array',
                    ];
                },
            ];
            break;
        case 'post-type':
            $spec = [
                'menu_icon' => $this->makeRegExValidator(
                    '/^[a-z0-9_-]+$/'
                ),
                'labels' => function ($value) {
                    return [
                        'result' => is_array($value),
                        'message' => 'labels should be an array',
                    ];
                },
                'public' => function ($value) {
                    return [
                        'result' => is_bool($value),
                        'message' => 'public should be a bool',
                    ];
                },
                'show_ui' => function ($value) {
                    return [
                        'result' => is_bool($value),
                        'message' => 'show_ui should be a bool',
                    ];
                },
                'show_in_menu' => function ($value) {
                    return [
                        'result' => is_bool($value),
                        'message' => 'show_in_menu should be a bool',
                    ];
                },
                'menu_position' => function ($value) {
                    return [
                        'result' => is_int($value),
                        'message' => 'menu_position should be an int',
                    ];
                },
            ];
            break;
        case 'taxonomy':
            $spec = [
                'args' => [
                    'label' => $this->makeRegExValidator(
                        '/^.{1,100}$/'
                    ),
                    'labels' => function ($value) {
                        return [
                            'result' => is_array($value),
                            'message' => 'labels should be an array',
                        ];
                    },
                ],
                'object_type' => function ($value) {
                    return [
                        'result' => is_array($value),
                        'message' => 'object_type should be an array',
                    ];
                },
            ];
            break;
        case 'section':
            $spec = [
                'id' => $this->makeRegExValidator(
                    '/^[a-z0-9_-]+$/'
                ),
                'label' => $this->makeRegExValidator(
                    '/^.{1,100}$/'
                ),
                'description' => $this->makeRegExValidator(
                    '/^.{1,100}$/'
                ),
                'fields' => function ($value) {
                    return [
                        'result' => is_array($value),
                        'message' => 'fields should be an array',
                    ];
                },
            ];
            break;
        case 'controller':
            $spec = [
                'namespace' => $this->makeRegExValidator(
                    '/^[A-Za-z0-9_-]+$/'
                ),
            ];
            break;
        case 'route':
            $spec = function ($value) {
                return [
                    'result' => is_array($value),
                    'message' => 'spec should be an array',
                ];
            };
            break;
        case 'options':
            $spec = [
                'title' => $this->makeRegExValidator(
                    '/^.{1,100}$/'
                ),
                'menu-slug' => $this->makeRegExValidator(
                    '/^[a-z0-9_-]+$/'
                ),
                'capability' => $this->makeRegExValidator(
                    '/^[a-z0-9_-]+$/'
                ),
                'fields' => function ($value) {
                    return [
                        'result' => is_array($value),
                        'message' => 'fields should be an array',
                    ];
                },
            ];
            break;
        default:
            $spec = [];
            break;
        }

        return [
            'name' => $this->makeRegExValidator(
                '/^[a-z0-9_-]+$/'
            ),
            'spec' => $spec,
            'kind' => $this->makeRegExValidator(
                '/^post-type|taxonomy|meta|section|controller|route|options|extension$/'
            ),
            'enabled' => function ($value) {
                return [
                    'result' => is_bool($value),
                    'message' => 'enabled should be a boolean',
                ];
            },
        ];
    }

    /**
     * Returns the needed loader name.
     *
     * @param string $driver_name the driver's name
     *
     * @return string the loader method name
     *
     * @static
     *
     * @since  Method available since Release 3.0.0
     */
    public static function getLoaderMethod($driver_name)
    {
        return 'load'.ucwords($driver_name);
    }

    /**
     * Returns the needed parser name.
     *
     * @param string $driver_name the driver's name
     *
     * @return string the parser method name
     *
     * @static
     *
     * @since  Method available since Release 3.0.0
     */
    public static function getParserMethod($driver_name)
    {
        return 'parse'.ucwords($driver_name);
    }

    /**
     * Registers a driver in the Config Loader.
     *
     * @param DriverInterface $driver      the driver's object
     * @param bool            $is_selected whether $driver is the active one
     *
     * @return bool the driver was succesfully added
     *
     * @throws InvalidNamespaceException when the namespace interfers with Aeria
     * @throws DriverException           the adding of the driver fails
     *
     * @since  Method available since Release 3.0.0
     */
    public function addDriver(DriverInterface $driver, bool $is_selected = false): bool
    {
        $driver_name = $driver->getDriverName();
        if ($is_selected) {
            $this->active_driver = ucwords($driver_name);
        }

        $this->drivers[$driver_name] = get_class($driver);

        $extend_result_parser = static::extend(
            static::getParserMethod($driver_name),
            function (string $input) use ($driver) {
                return $driver->parse($input);
            }
        );

        $extend_result_driver = static::extend(
            static::getLoaderMethod($driver_name),
            function (array $data, ?string $namespace = 'aeria') use ($driver) {
                if ($namespace == 'aeria') {
                    throw new InvalidNamespaceException(
                        'aeria is a protected namespace'
                    );
                }
                $namespaceTree = static::createNamespaceTree(
                    $namespace,
                    '.',
                    $data
                );

                return $this->loadArray($namespaceTree);
            }
        );

        if (!$extend_result_parser || !$extend_result_driver) {
            throw new DriverException("Error adding config driver '$driver_name'");
        }

        return $extend_result_parser && $extend_result_driver;
    }

    /**
     * Returns the namespace mapped list.
     *
     * @param string $namespace the namespace
     * @param string $separator the namespace separator
     * @param array  $element   the inital element for reduce
     *
     * @return array the mapped list
     *
     * @static
     *
     * @since  Method available since Release 3.0.0
     */
    protected static function createNamespaceTree(
        string $namespace,
        string $separator,
        $element = []
    ): array {
        $splitted = explode($separator, $namespace);
        $mappedList = array_reduce(
            array_reverse($splitted), function ($acc, $key) {
                $newArray = [];
                $newArray[$key] = $acc;

                return $newArray;
            },
            $element
        );

        return $mappedList;
    }

    /**
     * Checks validity of standard configurations.
     *
     * @param array $data the configuration
     *
     * @return bool whether the configuration is valid
     *
     * @since  Method available since Release 3.0.0
     */
    public function isValidStandardConfiguration($data)
    {
        $this->_kind = $data['kind'];
        $exeption = $this->isValid($data);
        if (!is_null($exeption)) {
            throw $exeption;
        }
    }

    /**
     * Adds an array of drivers.
     *
     * @param array $drivers the additional drivers
     *
     * @return bool whether the drivers were successfully added
     *
     * @since  Method available since Release 3.0.0
     */
    public function addDrivers(array $drivers): bool
    {
        $result = true;
        foreach ($drivers as $driver) {
            $result &= $this->addDriver($driver);
        }

        return $result;
    }

    /**
     * Loads an array of configs.
     *
     * @param array $array the configs
     *
     * @return bool whether the loading was successful
     *
     * @since  Method available since Release 3.0.0
     */
    public function loadArray(array $array): bool
    {
        return $this->merge($array);
    }

    /**
     * Returns the saved drivers.
     *
     * @return array the saved drivers
     *
     * @since  Method available since Release 3.0.0
     */
    public function getDrivers(): array
    {
        return $this->drivers;
    }

    /**
     * Returns the saved root paths.
     *
     * @return array the root paths
     *
     * @since  Method available since Release 3.0.0
     */
    public function getRootPath(): array
    {
        return $this->root_paths;
    }

    /**
     * Adds a root path.
     *
     * @param string $root_path the additional path
     *
     * @throws Exception when $root_path is not a directory
     *
     * @since  Method available since Release 3.0.0
     */
    public function setRootPath(string $root_path)
    {
        if (!is_dir($root_path)) {
            throw new \Exception("{$root_path} is not a directory");
        }
        $this->root_paths[] = $root_path;
    }

    /**
     * Gets the current driver or the requested one.
     *
     * @param string $file_name The requested driver
     *
     * @return string the driver name
     *
     * @since  Method available since Release 3.0.0
     */
    public function getDriverInUse(string $file_name = null): string
    {
        if (!is_null($file_name)) {
            return $this->getDriverNameFromExtension(
                pathinfo($file_name, PATHINFO_EXTENSION)
            );
        }

        return $this->active_driver;
    }

    /**
     * Gets a driver name from a file extension.
     *
     * @param string $ext the file extension
     *
     * @return string the driver name
     *
     * @throws DriverException when there's no available driver
     *
     * @since  Method available since Release 3.0.0
     */
    public function getDriverNameFromExtension(string $ext): string
    {
        if (isset($this->drivers[$ext])) {
            return $ext;
        }
        throw new DriverException("No driver available for files with extension .{$ext}");
    }
}
