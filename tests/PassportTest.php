<?php

use Laravel\Passport\Passport;

class PassportTest extends PHPUnit_Framework_TestCase
{
    public function test_scopes_can_be_managed()
    {
        Passport::tokensCan([
            'user' => 'get user information',
        ]);

        $this->assertTrue(Passport::hasScope('user'));
        $this->assertEquals(['user'], Passport::scopeIds());
        $this->assertEquals('user', Passport::scopes()[0]->id);
    }

    public function test_public_scopes()
    {
        Passport::tokensCan([
            'user' => 'get user information',
            'users' => 'access to all the users information',
        ]);

        Passport::setPublicScopes(['user', 'invalid']);

        $this->assertEquals([
            'user' => 'get user information',
        ], Passport::getPublicScopes());

        $this->assertEquals(['user'], Passport::$publicScopes);
    }
}
