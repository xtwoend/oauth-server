<?php

namespace OAuthServer\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use OAuthServer\Exception\AuthenticationException;
use League\OAuth2\Server\Exception\OAuthServerException;


/**
 * @Controller()
 */
class TokenIssueController
{
    /**
     * @RequestMapping(path="/oauth/token", methods="post")
     */
    public function issueToken(RequestInterface $request, ResponseInterface $response)
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