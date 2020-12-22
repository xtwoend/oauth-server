<?php

namespace OAuthServer;

use App\Application;
use Psr\Container\ContainerInterface;
use League\OAuth2\Server\AuthorizationServer;
use OAuthServer\Repositories\TokenRepository;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use League\OAuth2\Server\Exception\OAuthServerException;

class RouteRegistry
{
    protected $app;
    protected $router;
    protected $container;
    protected $server;
    protected $tokens;

    public function __construct(
        ContainerInterface $container, 
        AuthorizationServer $server, 
        TokenRepository $tokens
    ) {
        $this->container = $container;
        $this->server    = $server;
        $this->tokens    = $tokens;
    }

    public function bind(Application $app)
    {
        $this->router = $app->router;
        
        $this->router->post('/oauth/token', 
            function(RequestInterface $request, ResponseInterface $response){
                return $this->issueToken($request, $response);
        });
    }

    public function issueToken(RequestInterface $request, ResponseInterface $response)
    {
        try {
            return $this->server->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse($response);
        } catch (\Exception $e) {
            $body = $response->getBody();
            $body->write($e->getMessage());
            return $response->withStatus(500)->withBody($body);
        }
    }
}
