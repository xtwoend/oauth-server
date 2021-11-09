<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace OAuthServer\Repositories;

use Hyperf\DbConnection\Db;
use OAuthServer\Event\RefreshTokenCreated;
use OAuthServer\Entities\RefreshTokenEntity;
use Psr\EventDispatcher\EventDispatcherInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    protected $events;

    public function __construct(EventDispatcherInterface $events)
    {
        $this->events = $events;
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        Db::connection(config('oauth.provider', 'default'))->table('oauth_refresh_tokens')->insert([
            'id' => $id = $refreshTokenEntity->getIdentifier(),
            'access_token_id' => $accessTokenId = $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'revoked' => false,
            'expires_at' => $refreshTokenEntity->getExpiryDateTime(),
        ]);

        $this->events->dispatch(new RefreshTokenCreated($id, $accessTokenId));
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        Db::connection(config('oauth.provider', 'default'))->table('oauth_refresh_tokens')->where('id', $tokenId)->update(['revoked' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        if ($token = Db::connection(config('oauth.provider', 'default'))->table('oauth_refresh_tokens')->where('id', $tokenId)->first()) {
            return $token->revoked;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken()
    {
        return new RefreshTokenEntity();
    }
}
