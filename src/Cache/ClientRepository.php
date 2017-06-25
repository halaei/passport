<?php

namespace Laravel\Passport\Cache;

use Illuminate\Contracts\Cache\Repository;
use Laravel\Passport\Client;

class ClientRepository extends \Laravel\Passport\ClientRepository
{
    /**
     * The cache
     *
     * @var Repository
     */
    private $cache;

    /**
     * ClientRepository constructor.
     *
     * @param Repository $cache
     */
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    public function find($id)
    {
        $cached = $this->cache->get($this->cacheKey($id));
        if ($cached instanceof Client) {
            return $cached;
        }
        $fresh = parent::find($id);
        if ($fresh) {
            $this->cache->put($this->cacheKey($id), $fresh, 60);
        }

        return $fresh;
    }

    public function update(Client $client, $name, $redirect)
    {
        $client = parent::update($client, $name, $redirect);
        $this->cache->forget($this->cacheKey($client->id));
        return $client;
    }

    public function regenerateSecret(Client $client)
    {
        $client = parent::regenerateSecret($client);
        $this->cache->forget($this->cacheKey($client->id));
        return $client;
    }

    public function revoked($id)
    {
        $client = $this->find($id);
        return ! $client || $client->revoked;
    }

    public function delete(Client $client)
    {
        parent::delete($client);

        $this->cache->forget($this->cacheKey($client->id));
    }

    /**
     * Get the cache key of a client.
     *
     * @param $id
     * @return string
     */
    private function cacheKey($id)
    {
        return '_passport:client:'.$id;
    }
}
