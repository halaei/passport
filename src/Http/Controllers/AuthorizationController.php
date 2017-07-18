<?php

namespace Laravel\Passport\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Laravel\Passport\Bridge\User;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\ClientRepository;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response as Psr7Response;
use League\OAuth2\Server\AuthorizationServer;
use Illuminate\Contracts\Routing\ResponseFactory;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;

class AuthorizationController
{
    use HandlesOAuthErrors;

    /**
     * The authorization server.
     *
     * @var AuthorizationServer
     */
    protected $server;

    /**
     * The response factory implementation.
     *
     * @var ResponseFactory
     */
    protected $response;

    /**
     * Create a new controller instance.
     *
     * @param  AuthorizationServer  $server
     * @param  ResponseFactory  $response
     * @return void
     */
    public function __construct(AuthorizationServer $server, ResponseFactory $response)
    {
        $this->server = $server;
        $this->response = $response;
    }

    /**
     * Authorize a client to access the user's account.
     *
     * @param  ServerRequestInterface  $psrRequest
     * @param  Request  $request
     * @param  ClientRepository  $clients
     * @param  TokenRepository  $tokens
     * @return \Illuminate\Http\Response
     */
    public function authorize(ServerRequestInterface $psrRequest,
                              Request $request,
                              ClientRepository $clients,
                              TokenRepository $tokens)
    {
        return $this->withErrorHandling(function () use ($psrRequest, $request, $clients, $tokens) {
            $authRequest = $this->server->validateAuthorizationRequest($psrRequest);

            $client = $clients->find($authRequest->getClient()->getIdentifier());

            $scopes = $this->parseScopes($authRequest, $client);

            $user = $request->user();

            if (! $client->trusted_client) {
                $token = $tokens->findValidToken($user, $client);
                if (! $token || $token->scopes !== collect($scopes)->pluck('id')->all()) {
                    $request->session()->put('authRequest', $authRequest);

                    return $this->response->view('passport::authorize', [
                        'client' => $client,
                        'user' => $user,
                        'scopes' => $scopes,
                        'request' => $request,
                    ]);
                }
            }

            return $this->approveRequest($authRequest, $user);
        });
    }

    /**
     * Transform the authorization requests's scopes into Scope instances.
     *
     * @param  AuthorizationRequest  $authRequest
     * @param Client $client
     *
     * @return array
     */
    protected function parseScopes($authRequest, Client $client)
    {
        $scopes = collect($authRequest->getScopes())->map(function ($scope) {
            return $scope->getIdentifier();
        })->all();

        if (in_array('*', $scopes)) {
            $scopes = array_values(array_intersect($client->getScopes(), Passport::$publicScopes));
        } else {
            $scopes = array_values(array_intersect($scopes, $client->getScopes(), Passport::$publicScopes));
        }

        return Passport::scopesFor(
            $scopes
        );
    }

    /**
     * Approve the authorization request.
     *
     * @param  AuthorizationRequest  $authRequest
     * @param  Model  $user
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function approveRequest($authRequest, $user)
    {
        $authRequest->setUser(new User($user->getKey()));

        $authRequest->setAuthorizationApproved(true);

        return $this->server->completeAuthorizationRequest(
            $authRequest, new Psr7Response
        );
    }
}
