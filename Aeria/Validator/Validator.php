<?php

namespace Aeria\Validator;

use Aeria\Validator\Exceptions\InvalidValidatorException;
use Aeria\Validator\Types\Callables\IsEmailValidator;
use Aeria\Validator\Types\RegEx\IsShortValidator;

/**
 * Validator is the service in charge of validating fields
 * 
 * @category Validator
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class Validator
{
    private $validators = [];
    private $coreValidators = [ IsEmailValidator::class, IsShortValidator::class ];
    /**
     * Constructs the service
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct()
    {
        foreach ($this->coreValidators as $validator) {
            $this->register($validator::getKey(), $validator::getValidator(), $validator::getMessage());
        }
    }

    /**
     * Registers a new validator
     * 
     * @param string $name          the validator name
     * @param mixed  $new_validator the validator to add
     * @param string $message       the error message
     * 
     * @return void
     * @throws InvalidValidatorException if the validator is wrong
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function register($name, $new_validator,$message=null)
    {
        if (is_callable($new_validator)) {
            $this->validators[$name] = $new_validator;
        } elseif (preg_match($new_validator, null) !== false) {
            $this->validators[$name] = function ($field) use ($new_validator, $message) {
                $isValid["status"] = (bool)preg_match($new_validator, $field);
                if ($isValid["status"] == false) {
                    $isValid["message"] = ($message!=null) ? $message : "The RegEx ".$new_validator." was not satisfied. ";
                }
                return $isValid;
            };
        } else {
            throw new InvalidValidatorException("The " . $name . " validator contains a wrong condition: " . $new_validator);
        }
    }
    /**
     * Registers a validator
     * 
     * @param AbstractValidator $validator the new validator
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function registerValidatorClass($validator)
    {
        $this->register($validator::getKey(), $validator::getValidator(), $validator::getMessage());
    }
    /**
     * Validates a field with a list of validators
     *
     * @param mixed $field        the field to validate
     * @param mixed $validations the required validations 
     * 
     * @return array the result of the validation
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function validate($field, $validations)
    {
        $validators=$this->validatorsToArray($validations);

        if ($validators == [""])
            return ["value" => $field, "status" => false];

        $validation["message"] = [];

        foreach ($validators as $key) {
            $result = $this->validators[$key]($field);
            if (isset($result['message'])) {
                $validation["message"][] = $result['message'];
            }
        }

        $validation["value"] = $field;
        $validation["status"] = (bool) count($validation["message"]);
        return $validation;
    }

    /**
     * Validates a field by its ID
     * 
     * @param string $full_id   the field's ID
     * @param mixed  $value     the field's value
     * @param array  $metaboxes the meta configuration
     *
     * @return array the results of the validation
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function validateByID($full_id, $value, $metaboxes)
    {
        foreach ($metaboxes as $id => $metabox) {
            $meta_id_length = strlen($id);
            if ($id == substr($full_id, 0, $meta_id_length)) {
                $metabox_id = substr($full_id, 0, $meta_id_length);
                $field_id = substr($full_id, $meta_id_length+1);
            }
        }
        if (!isset($metabox_id)) {
            return [
                'value' => $value,
                'status' => true,
                'message' => [
                    "An invalid field ID was inserted."
                ]
            ];
        }
        foreach ($metaboxes[$metabox_id]['fields'] as $index => $field) {
            if ($field['id'] == $field_id) {
                $validators = array_key_exists("validators", $field) ? $field['validators'] : "";
            }
        }
        return $this->validate($value, $validators);
    }

    /**
     * Transforms a string of validators to an array
     *
     * @param string $validators the input validators
     * 
     * @return array the validators
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function validatorsToArray($validators)
    {
        if (is_array($validators)) {
            return $validators;
        } return explode("|", $validators);
    }
}
