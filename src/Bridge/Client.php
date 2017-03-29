<?php

namespace Laravel\Passport\Bridge;

use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class Client implements ClientEntityInterface
{
    use ClientTrait, EntityTrait;

    /**
     * @var array
     */
    protected $scopes;

    /**
     * Create a new client instance.
     *
     * @param  string  $identifier
     * @param  string  $name
     * @param  string|string[]  $redirectUri
     * @param  array  $scopes
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
     * @param array $scopes
     * @return array
     */
    public function filterScopes(array $scopes)
    {
        if (in_array('*', $scopes) && in_array('*', $this->scopes)) {
            return ['*'];
        }
        $accepted = [];
        foreach ($scopes as $scope) {
            if ($scope != '*' && in_array($scope, $this->scopes)) {
                $accepted[] = $scope;
            }
        }
        return $accepted;
    }
}
