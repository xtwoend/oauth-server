<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace OAuthServer\Repositories;

use RuntimeException;
use Hyperf\DbConnection\Db;
use OAuthServer\Entities\UserEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $provider = $clientEntity->provider ?: config('oauth.provider');

        if (is_null($config = config('databases.'.$provider, null))) {
            throw new RuntimeException('Unable to determine authentication model from configuration.');
        }
        
        $query = Db::connection($provider);
        
        $user = $query->table('users')->where(config('oauth.find_by', 'email'), $username)->first();

        if (! $user) {
            return;
        }

        if(!password_verify($password, $user->password)){
            return;
        }

        return new UserEntity($user->id);
    }

    public function getUserById($id)
    {
        # code...
    }

    public function getUserByProviderUserId($id, $client)
    {
        $provider = $client->provider;
       
        if(is_null($provider))
            $provider = config('oauth.provider');

        $query = Db::connection($provider);
        $user = $query->table('users')->find($id);
        unset($user->password);
        return $user;
    }
}
