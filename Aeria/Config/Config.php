<?php

namespace Aeria\Config;

use Aeria\Config\Interfaces\DriverInterface;
use Aeria\Container\Interfaces\ExtensibleInterface;
use Aeria\Config\Traits\ValidateConfTrait;
use Aeria\Container\Interfaces\ValidateConfInterface;
use JsonSerializable;
use Aeria\Structure\Traits\{
    ExtensibleTrait,
    DictionaryTrait
};
use Aeria\Config\Exceptions\{
    DriverException,
    InvalidNamespaceException
};

class Config implements ExtensibleInterface, JsonSerializable, ValidateConfInterface
{
    use ExtensibleTrait;
    use DictionaryTrait {
        DictionaryTrait::__construct as instanciateDictionary;
    }
    use ValidateConfTrait;

    protected $drivers = [];
    protected $root_path = '';
    protected $active_driver = 'json';
    private $_kind;

    public function __construct()
    {
        $this->instanciateDictionary();
        $this->root_path = get_template_directory() . '/aeria-config';
    }

    public function getValidationStructure() : array
    {
        switch ($this->_kind){
        case 'meta':
            $spec = [
                'title' => $this->makeRegExValidator(
                    "/^.{1,30}$/"
                ),
                'context' => $this->makeRegExValidator(
                    "/^normal|side|advanced$/"
                ),
                'post_type' => function ($value) {
                    return [
                        'result' => is_array($value),
                        'message' => 'post_type should be an array'
                    ];
                },
                'fields' => function ($value) {
                    return [
                        'result' => is_array($value),
                        'message' => 'fields should be an array'
                    ];
                }
            ];
            break;
        case 'post-type':
            $spec =  [
                'menu_icon' => $this->makeRegExValidator(
                    "/^[a-z0-9_-]{1,30}$/"
                ),
                'labels' => function ($value) {
                    return [
                        'result' => is_array($value),
                        'message' => 'labels should be an array'
                    ];
                },
                'public' => function ($value) {
                    return [
                        'result' => is_bool($value),
                        'message' => 'public should be a bool'
                    ];
                },
                'show_ui' => function ($value) {
                    return [
                        'result' => is_bool($value),
                        'message' => 'show_ui should be a bool'
                    ];
                },
                'show_in_menu' => function ($value) {
                    return [
                        'result' => is_bool($value),
                        'message' => 'show_in_menu should be a bool'
                    ];
                },
                'menu_position' => function ($value) {
                    return [
                        'result' => is_int($value),
                        'message' => 'menu_position should be an int'
                    ];
                }
            ];
            break;  
        case 'taxonomy':
            $spec =  [
                'label' => $this->makeRegExValidator(
                    "/^.{1,30}$/"
                ),
                'labels' => function ($value) {
                    return [
                        'result' => is_array($value),
                        'message' => 'labels should be an array'
                    ];
                }
            ];
            break;   
        case 'section':
            $spec =  [
                'id' => $this->makeRegExValidator(
                    "/^[a-z0-9_-]{1,20}$/"
                ),
                'label' => $this->makeRegExValidator(
                    "/^.{1,30}$/"
                ),
                'description' => $this->makeRegExValidator(
                    "/^.{1,60}$/"
                ),
                'fields' => function ($value) {
                    return [
                        'result' => is_array($value),
                        'message' => 'fields should be an array'
                    ];
                }
            ];
            break;
        case 'controller':
            $spec =  [
                'namespace' => $this->makeRegExValidator(
                    "/^[A-Za-z0-9_-]{1,30}$/"
                ),
            ];
            break;
        case 'route':
            $spec =  [
                'path' => $this->makeRegExValidator(
                    "/^[a-z0-9_-]{1,20}$/"
                ),
                'method' => $this->makeRegExValidator(
                    "/^POST|GET|PUT|DELETE$/"
                ),
                'handler' => $this->makeRegExValidator(
                    "/^[a-z0-9_-]{1,50}$/"
                )
            ];
            break;  
        case 'options':
            $spec =  [
                'title' => $this->makeRegExValidator(
                    "/^.{1,40}$/"
                ),
                'menu-slug' => $this->makeRegExValidator(
                    "/^[a-z0-9_-]{1,20}$/"
                ),
                'capability' => $this->makeRegExValidator(
                    "/^[a-z0-9_-]{1,30}$/"
                ),
                'parent' => $this->makeRegExValidator(
                    "/^[a-z0-9_-]{1,30}$/"
                ),
                'fields' => function ($value) {
                    return [
                        'result' => is_array($value),
                        'message' => 'fields should be an array'
                    ];
                }
            ];
            break; 
        default:
            $spec = [];
            break;
        }  
        return [
            'name' => $this->makeRegExValidator(
                "/^[a-z0-9_-]{1,20}$/"
            ),
            'spec' => $spec,
            'kind' => $this->makeRegExValidator(
                "/^post-type|taxonomy|meta|section|controller|route|options$/"
            )
        ];
    }

    public static function getLoaderMethod($driver_name)
    {
        return 'load' . ucwords($driver_name);
    }

    public static function getParserMethod($driver_name)
    {
        return 'parse' . ucwords($driver_name);
    }

    public function addDriver(DriverInterface $driver, bool $is_selected = false) : bool
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

    protected static function createNamespaceTree(
        string $namespace,
        string $separator,
        $element = []
    ) : array {
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

    public function isValidStandardConfiguration($data)
    {
        $this->_kind = $data['kind'];
        $exeption = $this->isValid($data);
        if (!is_null($exeption)) {
            throw $exeption;
        }
    }

    public function addDrivers(array $drivers) : bool
    {
        $result = true;
        foreach ($drivers as $driver) {
            $result &= $this->addDriver($driver);
        }
        return $result;
    }

    public function loadArray(array $array) : bool
    {
        return $this->merge($array);
    }

    public function getDrivers() : array
    {
        return $this->drivers;
    }

    public function getRootPath() : string
    {
        return $this->root_path;
    }

    public function setRootPath(string $root_path)
    {
        if (!is_dir($root_path)) {
            throw new Exception("{$root_path} is not a directory");
        }
        $this->root_path = $root_path;
    }

    public function getDriverInUse(string $file_name = null) : string
    {
        if (!is_null($file_name)) {
            return $this->getDriverNameFromExtension(
                pathinfo($file_name, PATHINFO_EXTENSION)
            );
        }
        return $this->active_driver;
    }

    public function getDriverNameFromExtension(string $ext): string
    {
        if (isset($this->drivers[$ext])) {
            return $ext;
        }
        throw new DriverException("No driver available for files with extension .{$ext}");
    }

}
