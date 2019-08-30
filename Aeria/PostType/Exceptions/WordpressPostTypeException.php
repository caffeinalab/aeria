<?php

namespace Aeria\PostType\Exceptions;

use Exception;

class WordpressPostTypeException extends Exception
{
    public function __construct(WP_Error $wp_error, string $contextMessage)
    {
        $code = $wp_error->get_error_code();
        $message = "
            WP_Code: {$error_code},
            WP_Message: {$wp_error->get_error_message($code)},
            Context_Message: {$contextMessage}
        ";
        parent::__construct($message, $code);
    }
}