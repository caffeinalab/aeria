<?php

namespace Aeria\Container\Interfaces;
/**
 * This interface describes what a class needs to validate configurations
 * 
 * @category Container
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
interface ValidateConfInterface
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
    public function isValid(array $to_validate);
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
    );
    /**
     * Returns the validation array
     *
     * The returned array contains the validators we need in the config.
     * It is structured as the config files.
     *
     * @return array the validators array
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getValidationStructure() : array;
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
    public function makeRegExValidator(string $regEx);
    /**
     * Returns a boolean validator, valid if true
     *
     * @return Closure the truthness validator
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function makeTruthyValidator();
    /**
     * Returns a boolean validator, valid if false
     *
     * @return Closure the falseness validator
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function makeFalselyValidator();
}
