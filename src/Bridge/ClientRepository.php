<?php

namespace Laravel\Passport\Bridge;

use Laravel\Passport\ClientRepository as ClientModelRepository;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * List of grant types accessible to public clients.
     *
     * @var array
     */
    public static $publicGrantTypes = [
        'authorization_code',
        'password',
        'refresh_token'
    ];

    /**
     * The client model repository.
     *
     * @var \Laravel\Passport\ClientRepository
     */
    protected $clients;

    /**
     * Create a new repository instance.
     *
     * @param  \Laravel\Passport\ClientRepository  $clients
     * @return void
     */
    public function __construct(ClientModelRepository $clients)
    {
        $this->clients = $clients;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier, $grantType,
                                    $clientSecret = null, $mustValidateSecret = true)
    {
        // First, we will verify that the client exists and is authorized to request the grant type.
        $record = $this->clients->findActive($clientIdentifier);

        if (! $record || ! $this->handlesGrant($record, $grantType)) {
            return;
        }

        // Then, we validate secret if the we must.
        if ($mustValidateSecret && ! $record->public_client && ! hash_equals($record->secret, (string) $clientSecret)) {
            return;
        }

        // Once we have an existing client record with verified secret, we will create this actual client instance.
        // Then we will be ready to return this client instance back out to the consuming methods and finish up.
        return new Client($clientIdentifier, $record->name, $record->redirect, $record->getScopes());
    }

    /**
     * Determine if the given client can handle the given grant type.
     *
     * @param  \Laravel\Passport\Client  $record
     * @param  string  $grantType
     * @return bool
     */
    protected function handlesGrant($record, $grantType)
    {
        // Public clients can only handle public grant types.
        if ($record->public_client && ! static::isPublicGrantType($grantType)) {
            return false;
        }

        // Personal access clients can only handle personal access type.
        if ($record->personal_access_client) {
            return $grantType === 'personal_access';
        }

        // Password grant type can only be handled by password clients.
        if ($grantType === 'password') {
            return $record->password_client;
        }

        // Personal access grant type can only be handled by personal access clients.
        if ($grantType === 'personal_access') {
            return $record->personal_access_client;
        }

        return true;
    }

    /**
     * Determine if a grant type is accessible to public clients.
     *
     * @param  string  $type
     * @return bool
     */
    protected static function isPublicGrantType($type)
    {
        return in_array($type, static::$publicGrantTypes);
    }
}
