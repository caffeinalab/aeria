<?php

namespace Aeria\Config\Traits;

use Aeria\Config\Exceptions\ConfigValidationException;
use Aeria\Config\Exceptions\NoCallableArgumentException;
use Closure;

// TODO: Rifattorizzare come una classe
trait ValidateConfTrait
{

    public function isValid(array $to_validate = [])
    {
        $message = static::validateStructure(
            $this->getValidationStructure(),
            $to_validate
        );
        return is_null($message) ? null : new ConfigValidationException($message);
    }

    public function combineOrValidator(...$args) : Closure
    {
        foreach ($args as $func) {
            if (!is_callable($func)) {
                throw NoCallableArgumentException("{$func} is not callable");
            }
        }
        return function ($value) use ($args) {
            return array_reduce(
                $args,
                function ($acc, $func) use ($value) {
                    $check = $func($value);
                    return array_merge(
                        [
                            'result' => $acc['result'] || $check['result']
                        ],
                        $check['result']
                        ? []
                        : [
                            'message' => $acc['result']
                            ? "{$check['message']}"
                            : "{$acc['message']}, {$check['message']}"
                        ]
                    );
                },
                ['result'  => true]
            );
        };
    }

    public function combineAndValidator(...$args) : Closure
    {
        foreach ($args as $func) {
            if (!is_callable($func)) {
                throw NoCallableArgumentException("{$func} is not callable");
            }
        }
        return function ($value) {
            return array_reduce(
                $args,
                function ($acc, $func) use ($value) {
                    $check = call_user_func($func, $value);
                    return array_merge(
                        [
                            'result' => $acc['result'] && $check['result']
                        ],
                        $check['result']
                        ? []
                        : [
                            'message' => $acc['result']
                            ? "{$check['message']}"
                            : "{$acc['message']}, {$check['message']}"
                        ]
                    );
                },
                ['result'  => true]
            );
        };
    }

    public function makeIsArrayValidator() : Closure
    {
        return function ($value) {
            if (!is_array($value)) {
                return [
                    'message' => "{$value} is not an array",
                    'result'  => false
                ];
            } else {
                return [
                    'result'  => true
                ];
            }
        };
    }

    public function makeIsEqualToValidator(string $string_value)
    {
        return function ($value) {
            if (is_string($value)) {
                if ($string_value !== $value) {
                    return [
                        'message' => "{$value} do not match {$string_value}",
                        'result'  => false
                    ];
                } else {
                    return [
                        'result'  => true
                    ];
                }
            } else {
                return [
                    'message' => "The value is not a string",
                    'result'  => false
                ];
            }
        };
    }

    public function makeRegExValidator(string $regEx) : Closure
    {
        return function ($value) use ($regEx) {
            $match = preg_match($regEx, $value);
            if (!$match) {
                return [
                    'message' => "{$value} do not match {$regEx}",
                    'result'  => false
                ];
            } else {
                return [
                    'result'  => true
                ];
            }
        };
    }

    public function makeTruthyValidator() : Closure
    {
        return function ($value) {
            if (!!$value) {
                return [
                    'result'  => true
                ];
            } else {
                return [
                    'result'  => false,
                    'message' => "{$value} is not True"
                ];
            }
        };
    }

    public function makeFalselyValidator() : Closure
    {
        return function ($value) {
            if (!!$value) {
                return [
                    'result'  => false,
                    'message' => "{$value} is not False"
                ];
            } else {
                return [
                    'result'  => true,
                ];
            }
        };
    }

    public function makeCustomValidator(func $validator): Closure
    {
        return function ($value) {
            try {
                $validator($value);
                return [
                    'result' => true
                ];
            } catch (\Throwable $th) {
                return [
                    'result' => false,
                    'message' => $th->getMessage()
                ];
            }
        };
    }

    public static function validateStructure(
        array $validation_structure,
        array $array_to_validate
    ) {
        foreach ($validation_structure as $key => $value) {
            if (isset($array_to_validate[$key])) {
                $error = static::handleClosure(
                    $array_to_validate[$key],
                    $value,
                    $key
                );
                if (!is_null($error)) {
                    return $error;
                }
            } else {
                return "key:{$key} is not present in the configuration";
            }
        }
        return null;
    }

    private static function handleClosure($elementToValidate, $closure, $key)
    {
        if (is_null($elementToValidate)) {
            return "key:{$key} is null";
        }
        if (is_array($closure) && is_array($elementToValidate)) {
            $rec_valid = static::validateStructure(
                $closure,
                $elementToValidate
            );
            if (!is_null($rec_valid)) {
                return $rec_valid;
            }
        }
        if (is_callable($closure)) {
            $is_valid = $closure($elementToValidate);
            if (!$is_valid['result']) {
                return "key:{$key} is not valid, {$is_valid['message']}";
            }
        } else {
            return "key:{$key} must be callable";
        }
    }
}
