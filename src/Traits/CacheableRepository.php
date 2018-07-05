<?php

namespace Chelout\Repository\Traits;

use App\Repositories\Helpers\CacheKeys;
use Chelout\Repository\Contracts\ScopeInterface;
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
            $this->model->getTable(),
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
    public function getCacheKey($args = null)
    {
        return sprintf(
            '%s@%s-%s',
            class_basename(get_called_class()),
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'],
            md5(serialize($args) . serialize($this->getScopes()))
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
     * Find data by id.
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        if (!$this->allowedCache('find') || $this->isSkippedCache()) {
            return parent::find($id, $columns);
        }

        $key = $this->getCacheKey(func_get_args());
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()
            ->remember($key, $minutes, function () use ($id, $columns) {
                return parent::find($id, $columns);
            });

        $this->resetModel();
        $this->resetQueryScope();

        return $value;
    }

    /**
     * Find data by field and value.
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findByField($field, $value = null, $columns = ['*'])
    {
        if (!$this->allowedCache('findByField') || $this->isSkippedCache()) {
            return parent::findByField($field, $value, $columns);
        }

        $key = $this->getCacheKey(func_get_args());
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()
            ->remember($key, $minutes, function () use ($field, $value, $columns) {
                return parent::findByField($field, $value, $columns);
            });

        $this->resetModel();
        $this->resetQueryScope();

        return $value;
    }

    /**
     * Find data by multiple fields.
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhere(array $where, $columns = ['*'])
    {
        if (!$this->allowedCache('findWhere') || $this->isSkippedCache()) {
            return parent::findWhere($where, $columns);
        }

        $key = $this->getCacheKey(func_get_args());
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()
            ->remember($key, $minutes, function () use ($where, $columns) {
                return parent::findWhere($where, $columns);
            });

        $this->resetModel();
        $this->resetQueryScope();

        return $value;
    }

    /**
     * Find data by multiple values in one field.
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhereIn($field, array $values, $columns = ['*'])
    {
        if (! $this->allowedCache('findWhere') || $this->isSkippedCache()) {
            return parent::findWhereIn($field, $values, $columns);
        }

        $key = $this->getCacheKey(func_get_args());
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()
            ->remember($key, $minutes, function () use ($field, $values, $columns) {
                return parent::findWhereIn($field, $values, $columns);
            });

        $this->resetModel();
        $this->resetQueryScope();

        return $value;
    }

    /**
     * Find data by excluding multiple values in one field.
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhereNotIn($field, array $values, $columns = ['*'])
    {
        if (!$this->allowedCache('findWhere') || $this->isSkippedCache()) {
            return parent::findWhereNotIn($field, $values, $columns);
        }

        $key = $this->getCacheKey(func_get_args());
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()
            ->remember($key, $minutes, function () use ($field, $values, $columns) {
                return parent::findWhereNotIn($field, $values, $columns);
            });

        $this->resetModel();
        $this->resetQueryScope();

        return $value;
    }

    /**
     * Retrieve all data of repository.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        if (! $this->allowedCache('all') || $this->isSkippedCache()) {
            return parent::all($columns);
        }

        $key = $this->getCacheKey(func_get_args());
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()
            ->remember($key, $minutes, function () use ($columns) {
                return parent::all($columns);
            });

        $this->resetModel();
        $this->resetQueryScope();

        return $value;
    }

    /**
     * Retrieve data array for populate field select.
     *
     * @param string      $column
     * @param string|null $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function pluck($column, $key = null)
    {
        if (!$this->allowedCache('all') || $this->isSkippedCache()) {
            return parent::pluck($columns, $key);
        }

        $key = $this->getCacheKey(func_get_args());
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()
            ->remember($key, $minutes, function () use ($columns, $key) {
                return parent::pluck($columns, $key);
            });

        $this->resetModel();
        $this->resetQueryScope();

        return $value;
    }

    /**
     * Retrieve all data of repository, paginated.
     *
     * @param null   $limit
     * @param array  $columns
     * @param string $method
     *
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*'], $method = 'paginate')
    {
        if (! $this->allowedCache('paginate') || $this->isSkippedCache()) {
            return parent::paginate($limit, $columns, $method);
        }

        $key = $this->getCacheKey(func_get_args());

        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()
            ->remember($key, $minutes, function () use ($limit, $columns, $method) {
                return parent::paginate($limit, $columns, $method);
            });

        $this->resetModel();
        $this->resetQueryScope();

        return $value;
    }



    /**
     * Find data by Scope.
     *
     * @param ScopeInterface $scope
     *
     * @return mixed
     */
    public function getByScope(ScopeInterface $scope)
    {
        if (! $this->allowedCache('getByScope') || $this->isSkippedCache()) {
            return parent::getByScope($scope);
        }

        $key = $this->getCacheKey(func_get_args());
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()
            ->remember($key, $minutes, function () use ($scope) {
                return parent::getByScope($scope);
            });

        $this->resetModel();
        $this->resetQueryScope();

        return $value;
    }
}
