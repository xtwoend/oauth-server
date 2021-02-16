<?php

namespace OAuthServer\Exception;

class AuthenticationException extends \Exception
{
    public function __construct($message = '', $code = 401)
    {
        parent::__construct($message, $code);
    }
}
