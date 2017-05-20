<?php

use Laravel\Passport\Http\Middleware\CheckClientCredentials;
use Illuminate\Http\Request;
use PassportTests\Base\TestCase;

class CheckClientCredentialsTest extends TestCase
{
    public function test_request_is_passed_along_if_token_is_valid()
    {
        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');
        $resourceServer->shouldReceive('validateAuthenticatedRequest')->andReturn($psr = Mockery::mock());
        $psr->shouldReceive('getAttribute')->with('oauth_user_id')->andReturn(1);
        $psr->shouldReceive('getAttribute')->with('oauth_client_id')->andReturn(1);
        $psr->shouldReceive('getAttribute')->with('oauth_access_token_id')->andReturn('token');
        $psr->shouldReceive('getAttribute')->with('oauth_scopes')->andReturn(['*']);
        $clients = Mockery::mock(\Laravel\Passport\ClientRepository::class);
        $clients->shouldReceive('revoked')->once()->andReturn(false);

        $middleware = new CheckClientCredentials($resourceServer, $clients);

        $request = Request::create('/');
        $request->headers->set('Authorization', 'Bearer token');

        $response = $middleware->handle($request, function () {
            return 'response';
        });

        $this->assertEquals('response', $response);

    }

    /**
     * @expectedException Illuminate\Auth\AuthenticationException
     */
    public function test_exception_is_thrown_when_client_is_revoked()
    {
        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');
        $resourceServer->shouldReceive('validateAuthenticatedRequest')->andReturn($psr = Mockery::mock());
        $psr->shouldReceive('getAttribute')->with('oauth_user_id')->andReturn(1);
        $psr->shouldReceive('getAttribute')->with('oauth_client_id')->andReturn(1);
        $psr->shouldReceive('getAttribute')->with('oauth_access_token_id')->andReturn('token');
        $psr->shouldReceive('getAttribute')->with('oauth_scopes')->andReturn(['*']);
        $clients = Mockery::mock(\Laravel\Passport\ClientRepository::class);
        $clients->shouldReceive('revoked')->once()->andReturn(true);

        $middleware = new CheckClientCredentials($resourceServer, $clients);

        $request = Request::create('/');
        $request->headers->set('Authorization', 'Bearer token');

        $middleware->handle($request, function () {
            return 'response';
        });
    }

    /**
     * @expectedException Illuminate\Auth\AuthenticationException
     */
    public function test_exception_is_thrown_when_oauth_throws_exception()
    {
        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');
        $resourceServer->shouldReceive('validateAuthenticatedRequest')->andReturnUsing(function () {
            throw new League\OAuth2\Server\Exception\OAuthServerException('message', 500, 'error type');
        });
        $clients = Mockery::mock(\Laravel\Passport\ClientRepository::class);

        $middleware = new CheckClientCredentials($resourceServer, $clients);

        $request = Request::create('/');
        $request->headers->set('Authorization', 'Bearer token');

        $middleware->handle($request, function () {
            return 'response';
        });

    }

    /**
     * @expectedException \Laravel\Passport\Exceptions\MissingScopeException
     */
    public function test_exception_is_thrown_if_token_does_not_have_required_scopes()
    {
        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');
        $resourceServer->shouldReceive('validateAuthenticatedRequest')->andReturn($psr = Mockery::mock());
        $psr->shouldReceive('getAttribute')->with('oauth_user_id')->andReturn(1);
        $psr->shouldReceive('getAttribute')->with('oauth_client_id')->andReturn(1);
        $psr->shouldReceive('getAttribute')->with('oauth_access_token_id')->andReturn('token');
        $psr->shouldReceive('getAttribute')->with('oauth_scopes')->andReturn(['foo','notbar']);
        $clients = Mockery::mock(\Laravel\Passport\ClientRepository::class);
        $clients->shouldReceive('revoked')->once()->andReturn(false);

        $middleware = new CheckClientCredentials($resourceServer, $clients);

        $request = Request::create('/');
        $request->headers->set('Authorization', 'Bearer token');

        $response = $middleware->handle($request, function () {
            return 'response';
        },'foo', 'bar');

    }

}
