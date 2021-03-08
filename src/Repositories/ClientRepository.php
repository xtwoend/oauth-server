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
use OAuthServer\Entities\ClientEntity;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier)
    {
        $record = $this->findActive($clientIdentifier);

        if (! $record) {
            return;
        }

        return new ClientEntity(
            $clientIdentifier,
            $record->name,
            $record->redirect,
            ! empty($record->secret),
            $record->provider,
            $record->project_id?? null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        $record = $this->findActive($clientIdentifier);

        if (! $record || ! $this->handlesGrant($record, $grantType)) {
            return false;
        }

        return empty($record->secret) || $this->verifySecret((string) $clientSecret, $record->secret);

        return true;
    }

    protected function handlesGrant($record, $grantType)
    {
        switch ($grantType) {
            case 'authorization_code':
                return ! ($record->personal_access_client || $record->password_client);
            case 'personal_access':
                return $record->personal_access_client && ! empty($record->secret);
            case 'password':
                return $record->password_client;
            case 'client_credentials':
                return ! empty($record->secret) && ! $record->password_client;
            default:
                return true;
        }
    }

    protected function verifySecret($clientSecret, $storedHash)
    {
        return (false)
                ? password_verify($clientSecret, $storedHash)
                : hash_equals($storedHash, $clientSecret);
    }

    public function findActive($clientIdentifier)
    {
        return Db::connection(config('oauth.provider', 'default'))->table('oauth_clients')
            ->where('revoked', 0)
            ->where('id', $clientIdentifier)
            ->first();
    }
}
