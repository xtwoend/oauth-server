<?php

namespace OAuthServer\Middleware;

use OAuthServer\Exception\MissingScopeException;
use OAuthServer\Exception\AuthenticationException;


/**
 * 
 */
trait ValidateScopeTrait
{
    /**
     * Validate token credentials.
     *
     * @param  $token
     * @return void
     *
     * @throws \OAuthServer\Exception\AuthenticationException
     */
    protected function validateCredentials($token)
    {   
        if (! $token) {
            throw new AuthenticationException;
        }
    }

    /**
     * Validate token credentials.
     *
     * @param  $token
     * @param  array  $scopes
     * @return void
     *
     * @throws \Laravel\Passport\Exceptions\MissingScopeException
     */
    protected function validateScopes($token, $scopes)
    {
        if (in_array('*', json_decode($token->scopes, true))) {
            return;
        }
        
        foreach ($scopes as $scope) {
            if (! $this->repository->can($token, $scope)) {
                throw new MissingScopeException($scope);
            }
        }
    }
}
