<?php

namespace OAuthServer\Middleware;

use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Server\MiddlewareInterface;
use OAuthServer\Repositories\UserRepository;
use OAuthServer\Middleware\ValidateScopeTrait;
use OAuthServer\Exception\AuthenticationException;
use OAuthServer\Repositories\AccessTokenRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class AuthMiddleware implements MiddlewareInterface
{
    use ValidateScopeTrait;
    
    protected $userRepository;
    protected $repository;
    protected $server;
    protected $client;
    protected $user;
    
    public function __construct(UserRepository $userRepository, AccessTokenRepository $repository, ResourceServer $server)
    {
        $this->userRepository = $userRepository;
        $this->repository = $repository;
        $this->server = $server;
    }

    public function process(Request $request, Handler $handler): ResponseInterface
    {   
        try {
            $request = $this->server->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $exception) {
            throw new AuthenticationException("Unauthorize: {$exception->getMessage()}");
        }

        $dispatched = $request->getAttribute(\Hyperf\HttpServer\Router\Dispatched::class);
        $scopes = $dispatched->handler->options['scopes']?? [];

        $this->validate($request, $scopes);

        // asign oauth client & user information
        $request = $request->withAttribute('client', $this->client);
        $request = $request->withAttribute('user', $this->user);

        return $handler->handle($request);
    }

    protected function validate($request, $scopes)
    {
        [$token, $client] = $this->repository->getTokenAndClientByTokenId($request->getAttribute('oauth_access_token_id'));

        if(is_null($token->user_id))
            throw new AuthenticationException("Unauthorize.");

        $user = $this->userRepository->getUserByProviderUserId($token->user_id, $client->provider);
        
        $this->client   = $client;
        $this->user     = $user;

        $this->validateCredentials($token);
        $this->validateScopes($token, $scopes);
    }
}
