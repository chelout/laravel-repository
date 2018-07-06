<?php

namespace Chelout\Repository\Traits;

use Chelout\Repository\Contracts\ScopeInterface;
use Chelout\Repository\Eloquent\BaseRepository;
use Exception;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use ReflectionObject;

trait CacheableRepository
{
    /**
     * @var CacheRepository
     */
    protected $cacheRepository = null;

    /**
     * @var \Chelout\Repository\Eloquent\BaseRepository
     */
    protected $repository;

    /**
     * Set Cache Repository.
     *
     * @param CacheRepository $repository
     *
     * @return $this
     */
    public function setCacheRepository(CacheRepository $repository)
    {
        $this->cacheRepository = $repository->tags(
            $this->getCacheTags()
        );

        return $this;
    }

    /**
     * Return instance of Cache Repository.
     *
     * @return CacheRepository
     */
    public function getCacheRepository()
    {
        if (is_null($this->cacheRepository)) {
            $this->cacheRepository = app(
                config('repository.cache.repository', 'cache')
            )->tags(
                $this->getCacheTags()
            );
        }

        return $this->cacheRepository;
    }

    /**
     * Get Cahce tags
     *
     * @return array
     */
    public function getCacheTags()
    {
        return [
            'repositories',
            $this->repository->getModel()->getTable(),
        ];
    }

    /**
     * Skip Cache.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skipCache($status = true)
    {
        $this->cacheSkip = $status;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSkippedCache()
    {
        $skipCacheParam = config('repository.cache.params.skip_cache', 'skip_cache');

        return request()->{$skipCacheParam} ? true : $this->cacheSkip ?? false;
    }

    /**
     * @param $method
     *
     * @return bool
     */
    protected function allowedCache($method)
    {
        $cacheEnabled = config('repository.cache.enabled', true);

        if (! $cacheEnabled) {
            return false;
        }

        $cacheOnly = $this->cacheOnly ?? config('repository.cache.allowed.only', null);
        $cacheExcept = $this->cacheExcept ?? config('repository.cache.allowed.except', null);

        if (is_array($cacheOnly)) {
            return in_array($method, $cacheOnly);
        }

        if (is_array($cacheExcept)) {
            return ! in_array($method, $cacheExcept);
        }

        if (is_null($cacheOnly) && is_null($cacheExcept)) {
            return true;
        }

        return false;
    }

    /**
     * Get Cache key.
     *
     * @param $args
     *
     * @return string
     */
    public function getCacheKey($method, $parameters = null)
    {
        return sprintf(
            '%s@%s-%s',
            class_basename(get_called_class()),
            $method,
            md5(serialize($parameters) . serialize($this->repository->getScopes()))
        );
    }

    /**
     * Get cache minutes.
     *
     * @return int
     */
    public function getCacheMinutes()
    {
        return $this->cacheMinutes ?? config('repository.cache.minutes', 30);
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (!$this->allowedCache('all') || $this->isSkippedCache()) {
            $this->repository->$method(...$parameters);
        }

        $key = $this->getCacheKey($method, $parameters);
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()
            ->remember($key, $minutes, function () use ($method, $parameters) {
                return $this->repository->$method(...$parameters);
            });

        $this->repository->resetModel();
        $this->repository->resetQueryScope();

        return $value;
    }
}
