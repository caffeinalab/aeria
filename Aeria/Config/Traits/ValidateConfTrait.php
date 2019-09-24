<?php

namespace Aeria\Config\Traits;

use Aeria\Config\Exceptions\ConfigValidationException;
use Aeria\Config\Exceptions\NoCallableArgumentException;
use Closure;

/**
 * ValidateConfTrait allows a class to validate configurations
 * 
 * @category Config
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
trait ValidateConfTrait
{
    /**
     * Checks if the passed configuration is valid
     *
     * @param array $to_validate the validatable configuration
     *
     * @return null|Exception null if the validation was ok
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function isValid(array $to_validate = [])
    {
        $message = static::validateStructure(
            $this->getValidationStructure(),
            $to_validate
        );
        return is_null($message) ? null : new ConfigValidationException($message);
    }
    /**
     * Combines validators with an OR condition
     *
     * @param array ...$args the required validators
     *
     * @return Closure the multiple validator
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Combines validators with an AND condition
     *
     * @param array ...$args the required validators
     *
     * @return Closure the multiple validator
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Returns an array validator
     *
     * @return Closure the isArray validator
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Returns a string comparator
     *
     * @param string $string_value the string to be compared to
     *
     * @return Closure the string validator
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Returns a RegEx validator
     *
     * @param string $regEx the regEx to validate with
     *
     * @return Closure the RegEx validator
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Returns a boolean validator, valid if true
     *
     * @return Closure the truthness validator
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Returns a boolean validator, valid if false
     *
     * @return Closure the falseness validator
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Returns a custom validator
     *
     * @param func $validator the function to validate with
     *
     * @return Closure the custom validator
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Validates a structure of validators vs. an array
     *
     * @param array $validation_structure the validation structure
     * @param array $array_to_validate    the array to validate
     *
     * @return null|string null if valid, string with the error if not
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function validateStructure(
        array $validation_structure,
        array $array_to_validate
    ) {
        foreach ($validation_structure as $key => $value) {
            if (isset($array_to_validate[$key])) {
                if (is_array($value)) {
                    $error = static::validateStructure($value, $array_to_validate[$key]);
                } else {
                    $error = static::handleClosure(
                        $array_to_validate[$key],
                        $value,
                        $key
                    );
                }
                if (!is_null($error)) {
                    return $error;
                }  
            }
        }
        return null;
    }
    /**
     * Validates a single element
     *
     * @param mixed   $element_to_validate the element we want to validate
     * @param Closure $closure             the function to validate the element with
     * @param string  $key                 the element's key
     *
     * @return Closure the RegEx validator
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    private static function handleClosure($element_to_validate, $closure, $key)
    {
        if (is_null($element_to_validate)) {
            return "key:{$key} is null";
        }
        if (is_array($closure) && is_array($element_to_validate)) {
            $rec_valid = static::validateStructure(
                $closure,
                $element_to_validate
            );
            if (!is_null($rec_valid)) {
                return $rec_valid;
            }
        }
        if (is_callable($closure)) {
            $is_valid = $closure($element_to_validate);
            if (!$is_valid['result']) {
                return "key:{$key} is not valid, {$is_valid['message']}";
            }
        } else {
            return "key:{$key} must be callable";
        }
    }
}
