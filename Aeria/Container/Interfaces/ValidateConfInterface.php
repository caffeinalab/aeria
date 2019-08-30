<?php

namespace Aeria\Container\Interfaces;

interface ValidateConfInterface
{
    public function isValid(array $to_validate);
    public static function validateStructure(
        array $validation_structure,
        array $array_to_validate
    );
    public function getValidationStructure() : array;
    public function makeRegExValidator(string $regEx);
    public function makeTruthyValidator();
    public function makeFalselyValidator();
}
