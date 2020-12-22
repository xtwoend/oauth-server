<?php

namespace OAuthServer\Exception;

class AuthenticationException extends \Exception
{
    public function __construct($message = '', $code = 1003) 
    {
        parent::__construct('Unauthorized', $code);
    }
}