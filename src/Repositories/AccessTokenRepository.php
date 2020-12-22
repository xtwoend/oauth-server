<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace OAuthServer\Repositories;

use DateTime;
use Hyperf\DbConnection\Db;
use OAuthServer\Event\AccessTokenCreated;
use OAuthServer\Entities\AccessTokenEntity;
use Psr\EventDispatcher\EventDispatcherInterface;
use OAuthServer\Repositories\FormatsScopesForStorage;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{   
    use FormatsScopesForStorage;
    
    protected $events;

    public function __construct(EventDispatcherInterface $events) 
    {
        $this->events = $events;   
    }
    
    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        Db::connection(config('oauth.provider', 'default'))->table('oauth_access_tokens')->insert([
            'id' => $accessTokenEntity->getIdentifier(),
            'user_id' => $accessTokenEntity->getUserIdentifier(),
            'client_id' => $accessTokenEntity->getClient()->getIdentifier(),
            'scopes' => $this->formatScopesForStorage($accessTokenEntity->getScopes()),
            'revoked' => false,
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
            'expires_at' => $accessTokenEntity->getExpiryDateTime(),
        ]);

        $this->events->dispatch(new AccessTokenCreated(
            $accessTokenEntity->getIdentifier(),
            $accessTokenEntity->getUserIdentifier(),
            $accessTokenEntity->getClient()->getIdentifier()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId)
    {
        Db::connection(config('oauth.provider', 'default'))->table('oauth_access_tokens')->where('id', $tokenId)->update(['revoked' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId)
    {
        if ($token = Db::connection(config('oauth.provider', 'default'))->table('oauth_access_tokens')->find($tokenId)) {
            return (bool) $token->revoked;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new AccessTokenEntity(
            $userIdentifier,
            $scopes,
            $clientEntity
        );
    }

    public function find($tokenId)
    {
        return Db::connection(config('oauth.provider', 'default'))
            ->table('oauth_access_tokens')
            ->find($tokenId);
    }

    public function getTokenAndClientByTokenId($tokenId): ?array
    {
        $token = Db::connection(config('oauth.provider', 'default'))
            ->table('oauth_access_tokens')
            ->where('revoked', 0)
            ->where('id', $tokenId)
            ->first();
        
        if(! $token)
            return [null, null];

        $client = Db::connection(config('oauth.provider', 'default'))
            ->table('oauth_clients')
            ->where('id', $token->client_id)
            ->first();
        
        unset($client->secret);

        return [$token, $client];
    }

    public function can($token, $scope)
    {
        $tokenScopes = \json_decode($token->scopes, true);

        if (in_array('*', $tokenScopes)) {
            return true;
        }

        $scopes = [$scope];

        foreach ($scopes as $scope) {
            if (array_key_exists($scope, array_flip($tokenScopes))) {
                return true;
            }
        }

        return false;
    }
}
