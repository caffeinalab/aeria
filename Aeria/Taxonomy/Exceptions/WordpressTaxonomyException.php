<?php

namespace Aeria\Taxonomy\Exceptions;

use Exception;

/**
 * WordpressTaxonomyException gets thrown when WP throws an exception
 * 
 * @category Taxonomy
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class WordpressTaxonomyException extends Exception
{
    /**
     * Contructs the exception from the WP one
     *
     * @param WP_Error $wp_error        the WP's exception
     * @param string   $context_message the message 
     * 
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct(WP_Error $wp_error, string $context_message)
    {
        $code = $wp_error->get_error_code();
        $message = "
            WP_Code: {$error_code},
            WP_Message: {$wp_error->get_error_message($code)},
            Context_Message: {$context_message}
        ";
        parent::__construct($message, $code);
    }
}