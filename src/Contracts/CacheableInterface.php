<?php

namespace Chelout\Repository\Contracts;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Interface CacheableInterface.
 */
interface CacheableInterface
{
    /**
     * Set Cache Repository.
     *
     * @param CacheRepository $repository
     *
     * @return self
     */
    public function setCacheRepository(CacheRepository $repository);

    /**
     * Return instance of Cache Repository.
     *
     * @return CacheRepository
     */
    public function getCacheRepository();

    /**
     * Get Cahce tags
     *
     * @return array
     */
    public function getCacheTags();

    /**
     * Get Cache key for the method.
     *
     * @param $method
     * @param $args
     *
     * @return string
     */
    public function getCacheKey($method, $parameters = null);

    /**
     * Get cache minutes.
     *
     * @return int
     */
    public function getCacheMinutes();

    /**
     * Skip Cache.
     *
     * @param bool $status
     *
     * @return self
     */
    public function skipCache($status = true);
}
