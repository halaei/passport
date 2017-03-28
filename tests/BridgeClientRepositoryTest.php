<?php

use Laravel\Passport\Bridge\Client;
use Laravel\Passport\Bridge\ClientRepository;
use Laravel\Passport\Client as PassportClient;
use Laravel\Passport\ClientRepository as PassportClientRepository;
use PassportTests\Base\TestCase;

class BridgeClientRepositoryTest extends TestCase
{
    /**
     * @var \Mockery\MockInterface|PassportClientRepository
     */
    private $clients;

    /**
     * @var ClientRepository
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->clients = Mockery::mock(PassportClientRepository::class);
        $this->repository = new ClientRepository($this->clients);
    }

    public function test_normal_client_access_to_grants()
    {
        $this->clients->shouldReceive('findActive')->with(1)->andReturn($this->getClient());

        $this->assertInstanceOf(Client::class, $this->repository->getClientEntity(1, 'client_credentials', 'secret', true));
        $this->assertInstanceOf(Client::class, $this->repository->getClientEntity(1, 'authorization_code', 'secret', true));
        $this->assertInstanceOf(Client::class, $this->repository->getClientEntity(1, 'refresh_token', 'secret', true));

        $this->assertNull($this->repository->getClientEntity(1, 'authorization_code', 'wrong-secret', true));
        $this->assertNull($this->repository->getClientEntity(1, 'client_credentials', 'wrong-secret', true));

        $this->assertNull($this->repository->getClientEntity(1, 'password', 'secret', true));
        $this->assertNull($this->repository->getClientEntity(1, 'personal_access', 'secret', true));
    }

    public function test_personal_client_access_to_grants()
    {
        $this->clients->shouldReceive('findActive')->with(1)->andReturn($this->getClient([
            'personal_access_client' => true,
        ]));

        $this->assertInstanceOf(Client::class, $this->repository->getClientEntity(1, 'personal_access', 'secret', true));

        $this->assertNull($this->repository->getClientEntity(1, 'authorization_code', 'secret', true));
        $this->assertNull($this->repository->getClientEntity(1, 'client_credentials', 'secret', true));
        $this->assertNull($this->repository->getClientEntity(1, 'password', 'secret', true));
        $this->assertNull($this->repository->getClientEntity(1, 'refresh_token', 'secret', true));
    }

    public function test_password_client_access_to_grants()
    {
        $this->clients->shouldReceive('findActive')->with(1)->andReturn($this->getClient([
            'password_client' => true,
        ]));
        $this->assertInstanceOf(Client::class, $this->repository->getClientEntity(1, 'password', 'secret', true));
        $this->assertInstanceOf(Client::class, $this->repository->getClientEntity(1, 'refresh_token', 'secret', true));
        $this->assertInstanceOf(Client::class, $this->repository->getClientEntity(1, 'authorization_code', 'secret', true));
        $this->assertInstanceOf(Client::class, $this->repository->getClientEntity(1, 'client_credentials', 'secret', true));

        $this->assertNull($this->repository->getClientEntity(1, 'personal_access', 'secret', true));
    }

    public function test_public_password_client_access_to_grants()
    {
        $this->clients->shouldReceive('findActive')->with(1)->andReturn($this->getClient([
            'public_client' => true,
            'password_client' => true,
        ]));

        $this->assertNull($this->repository->getClientEntity(1, 'authorization_code', 'secret', true));
        $this->assertNull($this->repository->getClientEntity(1, 'refresh_token', 'secret', true));
        $this->assertNull($this->repository->getClientEntity(1, 'client_credentials', 'secret', true));
        $this->assertNull($this->repository->getClientEntity(1, 'personal_access', 'secret', true));

        $this->assertInstanceOf(Client::class, $this->repository->getClientEntity(1, 'password', null, false));
    }

    private function getClient(array $values = [])
    {
        return new PassportClient(array_merge([
            'name' => 'Client',
            'redirect' => ['http://localhost'],
            'secret' => 'secret',
            'personal_access_client' => false,
            'password_client' => false,
            'public_client' => false,
        ], $values));
    }
}
