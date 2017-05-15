<?php

namespace Laravel\Passport\Cache;

use Illuminate\Contracts\Cache\Repository;
use Laravel\Passport\Token;

class TokenRepository extends \Laravel\Passport\TokenRepository
{
    /**
     * The cache
     *
     * @var Repository
     */
    private $cache;

    /**
     * TokenRepository constructor.
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
        if ($cached) {
            return $cached;
        }
        $fresh = parent::find($id);
        if ($fresh) {
            $this->cache->put($this->cacheKey($id), $fresh, 60);
        }

        return parent::find($id);
    }

    public function save(Token $token)
    {
        parent::save($token);

        $this->cache->forget($this->cacheKey($token->id));
    }

    public function revokeAccessToken($id)
    {
        $revoked = parent::revokeAccessToken($id);

        $this->cache->forget($this->cacheKey($id));

        return $revoked;
    }

    /**
     * Get the cache key of a client.
     *
     * @param $id
     * @return string
     */
    private function cacheKey($id)
    {
        return '_passport:token:'.$id;
    }
}
