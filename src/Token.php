<?php

namespace OAuthServer;

use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;
use Hyperf\HttpServer\Contract\ResponseInterface;
use OAuthServer\Exception\AuthenticationException;
use League\OAuth2\Server\Exception\OAuthServerException;


class Token
{
    protected $request;
    protected $response;
    protected $server;

    public function __construct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        AuthorizationServer $server
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->server = $server;
    }

    public function byUser($user, $client, array $scopes = [])
    {
        $request = $this->request->withParsedBody([
            'grant_type'    => 'user',
            'client_id'     => $client->id,
            'client_secret' => $client->secret,
            'scope'         => implode(' ', $scopes)
        ]);

        $request = $request->withAttribute('user', $user);

        return $this->issueToken($request, $this->response);
    }

    public function refreshToken($refreshToken, $client, array $scopes = [])
    {
        $request = $this->request->withParsedBody([
            'grant_type'    => 'refresh_token',
            'client_id'     => $client->id,
            'client_secret' => $client->secret,
            'refresh_token' => $refreshToken,
            'scope'         => implode(' ', $scopes)
        ]);
        
        return $this->issueToken($request, $this->response);
    }

    public function issueToken(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            return $this->server->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse($response);
        } catch (\Exception $e) {
            throw new AuthenticationException("Unauthorize: {$e->getMessage()}");
        }
    }
}