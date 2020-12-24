<?php

namespace OAuthServer\Middleware;

use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Server\MiddlewareInterface;
use OAuthServer\Middleware\ValidateScopeTrait;
use OAuthServer\Repositories\ClientRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class ClientMiddleware implements MiddlewareInterface
{
    use ValidateScopeTrait;
    
    protected $repository;
    protected $server;
    protected $client;

    public function __construct(ClientRepository $repository, ResourceServer $server)
    {
        $this->repository = $repository;
        $this->server = $server;
    }

    public function process(Request $request, Handler $handler): ResponseInterface
    {   
        try {
            $request = $this->server->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $exception) {
            throw new AuthenticationException("Unauthorize: {$exception->getMessage()}");
        } catch (\Exception $exception){
            throw new AuthenticationException("Unauthorize: {$exception->getMessage()}");
        }

        $dispatched = $request->getAttribute(\Hyperf\HttpServer\Router\Dispatched::class);
        $scopes = $dispatched->handler->options['scopes']?? [];

        $this->validate($request, $scopes);

        $request = $request->withAttribute('client', $this->client);

        return $handler->handle($request);
    }

    protected function validate($request, $scopes)
    {
        $client = $this->repository->findActive($request->getAttribute('oauth_client_id'));

        if(is_null($client))
            throw new AuthenticationException("Unauthorize.");

        $this->client   = $client;

        $tokenScope = $request->getAttribute('oauth_scopes')?? [];

        $this->validateScopes($tokenScope, $scopes);
    }
}
