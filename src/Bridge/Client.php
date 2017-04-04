<?php

namespace Laravel\Passport\Bridge;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class Client implements ClientEntityInterface
{
    use ClientTrait, EntityTrait;

    /**
     * @var string[]
     */
    protected $scopes;

    /**
     * Create a new client instance.
     *
     * @param  string  $identifier
     * @param  string  $name
     * @param  string|string[]  $redirectUri
     * @param  string[]  $scopes
     */
    public function __construct($identifier, $name, $redirectUri, array $scopes = ['*'])
    {
        $this->setIdentifier($identifier);

        $this->name = $name;
        $this->redirectUri = $redirectUri;
        $this->scopes = $scopes;
    }

    /**
     * Filter the scopes to the acceptable scopes of client.
     *
     * @param ScopeEntityInterface[] $scopes
     * @return array
     */
    public function filterScopes(array $scopes)
    {
        // If this is a super-client, return all the required scopes.
        if (in_array('*', $this->scopes)) {
            return $scopes;
        }

        $accepted = [];

        foreach ($scopes as $scope) {
            // If the super-scope is required, return all the client scopes.
            if ($scope->getIdentifier() == '*') {
                return array_map(function ($scope) {
                    return new Scope($scope);
                }, $this->scopes);
            }

            // Filter scopes that are not supported by the client.
            if (in_array($scope->getIdentifier(), $this->scopes)) {
                $accepted[] = $scope;
            }
        }

        return $accepted;
    }
}
