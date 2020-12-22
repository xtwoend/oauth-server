<?php

namespace OAuthServer;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use OAuthServer\Repositories\UserRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use OAuthServer\Repositories\ScopeRepository;
use OAuthServer\Repositories\ClientRepository;
use OAuthServer\Repositories\AuthCodeRepository;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use OAuthServer\Repositories\AccessTokenRepository;
use OAuthServer\Repositories\RefreshTokenRepository;

class AuthorizationServerFactory
{
    protected $container;
    protected $config;

    public function __construct(ContainerInterface $container) 
    {
        $this->container = $container;
        $this->config    = $container->get(ConfigInterface::class);
    }

    public function __invoke()
    {
        return tap($this->makeAuthorizationServer(), function ($server) {
            
            $scope = $this->config->get('oauth.scopes', []);
            $scope = implode(' ', array_keys($scope));
            $server->setDefaultScope($scope);

            $tokenExpiresIn = new \DateInterval('PT1H');

            $server->enableGrantType(
                new \League\OAuth2\Server\Grant\ClientCredentialsGrant(),
                $tokenExpiresIn
            );

            $server->enableGrantType(
                $this->makeAuthCodeGrant(), $tokenExpiresIn
            );

            $server->enableGrantType(
                $this->makeRefreshTokenGrant(), $tokenExpiresIn
            );

            $server->enableGrantType(
                $this->makePasswordGrant(), $tokenExpiresIn
            );
            
            return $server;
        });
    }

    /**
     * Make the authorization service instance.
     *
     * @return \League\OAuth2\Server\AuthorizationServer
     */
    public function makeAuthorizationServer()
    {
        return new AuthorizationServer(
            make(ClientRepository::class),         // instance of ClientRepositoryInterface
            make(AccessTokenRepository::class),    // instance of AccessTokenRepositoryInterface
            make(ScopeRepository::class),
            'file://' . BASE_PATH . '/var/oauth-private.key',    // path to private key
            $this->config->get('oauth.key', 'E3Wxizr8gUXuBuyG7CecmGX9E9lbRzdFmqQpG2yP85eDuXzqOj')
        );
    }

    public function makeAuthCodeGrant()
    {
        return new AuthCodeGrant(
            make(AuthCodeRepository::class),
            make(RefreshTokenRepository::class),
            new \DateInterval('PT10M')
        );
    }

    public function makeRefreshTokenGrant()
    {
        $repository = make(RefreshTokenRepository::class);

        return tap(new RefreshTokenGrant($repository), function ($grant) {
            $grant->setRefreshTokenTTL(new \DateInterval('P1M'));
        });
    }

    public function makePasswordGrant()
    {
        $userRepository = make(UserRepository::class);
        $refreshTokenRepository = make(RefreshTokenRepository::class);

        return tap(new PasswordGrant($userRepository, $refreshTokenRepository), function ($grant){
            $grant->setRefreshTokenTTL(new \DateInterval('P1M'));
        });
    }
}