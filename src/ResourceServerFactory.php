<?php

namespace OAuthServer;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use League\OAuth2\Server\ResourceServer;
use OAuthServer\Repositories\AccessTokenRepository;

class ResourceServerFactory
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
        return new ResourceServer(
            make(AccessTokenRepository::class),
            'file://' . BASE_PATH . '/var/oauth-public.key',
        );
    }
}