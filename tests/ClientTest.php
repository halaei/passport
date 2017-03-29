<?php

use Laravel\Passport\Client;
use PassportTests\Base\TestCase;

class ClientTest extends TestCase
{
    public function test_star_scope_is_the_default()
    {
        $client = new Client();
        $this->assertEquals(['*'], $client->getScopes());
    }
}
