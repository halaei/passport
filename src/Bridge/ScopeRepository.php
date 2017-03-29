<?php

namespace Laravel\Passport\Bridge;

use Laravel\Passport\Passport;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        if (Passport::hasScope($identifier)) {
            return new Scope($identifier);
        }
    }

    /**
     * @param  Client  $clientEntity
     * {@inheritdoc}
     */
    public function finalizeScopes(
        array $scopes, $grantType,
        ClientEntityInterface $clientEntity, $userIdentifier = null)
    {
        return $clientEntity->filterScopes(collect($scopes)->filter(function ($scope) {
            /** @var ScopeEntityInterface $scope */
            return Passport::hasScope($scope->getIdentifier());
        })->values()->all());
    }
}
