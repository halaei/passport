<?php

namespace Laravel\Passport\Http\Middleware;

use Closure;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Exceptions\MissingScopeException;
use League\OAuth2\Server\ResourceServer;
use Illuminate\Auth\AuthenticationException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class CheckClientCredentials
{
    /**
     * The Resource Server instance.
     *
     * @var ResourceServer
     */
    private $server;

    /**
     * The Client Repository instance.
     *
     * @var ClientRepository
     */
    private $clients;

    /**
     * Create a new middleware instance.
     *
     * @param  ResourceServer  $server
     * @param  ClientRepository  $clients
     * @return void
     */
    public function __construct(ResourceServer $server, ClientRepository $clients)
    {
        $this->server = $server;
        $this->clients = $clients;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        $psr = (new DiactorosFactory)->createRequest($request);

        try {
            $psr = $this->server->validateAuthenticatedRequest($psr);
        } catch (OAuthServerException $e) {
            throw new AuthenticationException;
        }

        $clientId = $psr->getAttribute('oauth_client_id');

        // Verify if the client that issued this token is still valid
        if ($this->clients->revoked($clientId)) {
            throw new AuthenticationException;
        }

        $this->validateScopes($psr, $scopes);

        $this->copyOAuthClaims($request, $psr);

        return $next($request);
    }

    /**
     * Validate the scopes on the incoming request.
     *
     * @param  \Psr\Http\Message\ResponseInterface
     * @param  array  $scopes
     * @return void
     *
     * @throws \Laravel\Passport\Exceptions\MissingScopeException
     */
    protected function validateScopes($psr, $scopes)
    {
        if (in_array('*', $tokenScopes = $psr->getAttribute('oauth_scopes'))) {
            return;
        }

        foreach ($scopes as $scope) {
            if (! in_array($scope, $tokenScopes)) {
                throw new MissingScopeException($scope);
            }
        }
    }

    /**
     * Copy Token Claims from PSR request to Laravel request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Psr\Http\Message\ServerRequestInterface $psr
     */
    protected function copyOAuthClaims($request, $psr)
    {
        $request['oauth_access_token_id'] = $psr->getAttribute('oauth_access_token_id');
        $request['oauth_client_id'] = $psr->getAttribute('oauth_client_id');
        $request['oauth_user_id'] = $psr->getAttribute('oauth_user_id');
        $request['oauth_scopes'] = $psr->getAttribute('oauth_scopes');
    }
}
