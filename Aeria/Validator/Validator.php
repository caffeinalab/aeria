<?php

namespace Aeria\Validator;

use Aeria\Validator\Exceptions\InvalidValidatorException;
use Aeria\Validator\Types\Callables\IsEmailValidator;
use Aeria\Validator\Types\RegEx\IsShortValidator;

class Validator
{
    private $validators = [];
    private $coreValidators = [ IsEmailValidator::class, IsShortValidator::class ];

    public function __construct()
    {
        foreach($this->coreValidators as $validator){
            $this->register($validator::getKey(), $validator::getValidator(), $validator::getMessage());
        }
    }


    public function register($name, $newValidator,$message=null)
    {
        if (is_callable($newValidator)) {
            $this->validators[$name] = $newValidator;
        } elseif (preg_match($newValidator, null) !== false) {
            $this->validators[$name] = function ($field) use ($newValidator, $message) {
                $isValid["status"] = (bool)preg_match($newValidator, $field);
                if ($isValid["status"] == false) {
                    $isValid["message"] = ($message!=null) ? $message : "The RegEx ".$newValidator." was not satisfied. ";
                }
                return $isValid;
            };
        } else {
          throw new InvalidValidatorException("The " . $name . " validator contains a wrong condition: " . $newValidator);
        }
    }

    public function registerValidatorClass($validator)
    {
        $this->register($validator::getKey(), $validator::getValidator(), $validator::getMessage());
    }

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


    public function validateByID ($full_id, $value, $metaboxes)
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
            if ($field['id'] == $field_id){
                $validators = array_key_exists("validators", $field) ? $field['validators'] : "";
            }
        }
        return $this->validate($value, $validators);
    }
    private function validatorsToArray ($validators)
    {
        if (is_array($validators)) {
            return $validators;
        } return explode("|", $validators);

    }
}
