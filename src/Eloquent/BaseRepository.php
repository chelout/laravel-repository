<?php

namespace Chelout\Repository\Eloquent;

use Chelout\Repository\Contracts\RepositoryInterface;
use Chelout\Repository\Contracts\RepositoryQueryScopeInterface;
use Chelout\Repository\Contracts\RepositoryScopeInterface;
use Chelout\Repository\Contracts\ScopeInterface;
use Chelout\Repository\Events\RepositoryEntityCreated;
use Chelout\Repository\Events\RepositoryEntityDeleted;
use Chelout\Repository\Events\RepositoryEntityUpdated;
use Chelout\Repository\Exceptions\RepositoryException;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class AbstractRepository.
 */
class BaseRepository implements RepositoryInterface, RepositoryQueryScopeInterface, RepositoryScopeInterface
{
    /**
     * @var string
     */
    protected $fqcn;
    
    /**
     * @var Model
     */
    protected $model;

    /**
     * Collection of Scopes.
     *
     * @var Collection
     */
    protected $scopes;

    /**
     * @var bool
     */
    protected $skipScope = false;

    /**
     * @var \Closure
     */
    protected $queryScope = null;

    /**
     * @param string $fqcn
     */
    public function __construct()
    {
        $this->resetScopes();
        $this->makeModel();
        $this->boot();
    }

    public function boot()
    {
        //
    }

    /**
     * @throws RepositoryException
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    /**
     * @return Model
     */
    public function makeModel()
    {
        $this->model = app($this->fqcn);

        return $this->model;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /*
     * Repository Interface implementation
     */

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
        $this->applyScopes();
        $this->applyQueryScope();

        $model = $this->model->findOrFail($id, $columns);

        $this->resetModel();

        return $model;
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
        $this->applyScopes();
        $this->applyQueryScope();

        $model = $this->model->where($field, '=', $value)->get($columns);

        $this->resetModel();

        return $model;
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
        $this->applyScopes();
        $this->applyQueryScope();

        $this->applyConditions($where);

        $model = $this->model->get($columns);
        $this->resetModel();

        return $model;
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
        $this->applyScopes();
        $this->applyQueryScope();

        $model = $this->model->whereIn($field, $values)->get($columns);

        $this->resetModel();

        return $model;
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
        $this->applyScopes();
        $this->applyQueryScope();

        $model = $this->model->whereNotIn($field, $values)->get($columns);

        $this->resetModel();

        return $model;
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
        $this->applyScopes();
        $this->applyQueryScope();

        if ($this->model instanceof Builder) {
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }

        $this->resetModel();
        $this->resetQueryScope();

        return $results;
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
        $this->applyScopes();

        return $this->model->pluck($column, $key);
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
        $this->applyScopes();
        $this->applyQueryScope();

        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        $results = $this->model->{$method}($limit, $columns);
        $results->appends(app('request')->query());

        $this->resetModel();

        return $results;
    }

    /**
     * Retrieve all data of repository, simple paginated.
     *
     * @param null  $limit
     * @param array $columns
     *
     * @return mixed
     */
    public function simplePaginate($limit = null, $columns = ['*'])
    {
        return $this->paginate($limit, $columns, 'simplePaginate');
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param string $column
     * @param string $direction
     *
     * @return self
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * Save a new entity in repository.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes)
    {
        $model = $this->model->newInstance($attributes);
        $model->save();

        $this->resetModel();

        event(new RepositoryEntityCreated($this, $model));

        return $model;
    }

    /**
     * Retrieve first data of repository, or return new Entity.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function firstOrNew(array $attributes = [])
    {
        $this->applyScopes();
        $this->applyQueryScope();

        $model = $this->model->firstOrNew($attributes);

        $this->resetModel();

        return $model;
    }

    /**
     * Retrieve first data of repository, or create new Entity.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function firstOrCreate(array $attributes = [])
    {
        $this->applyScopes();
        $this->applyQueryScope();

        $model = $this->model->firstOrCreate($attributes);

        $this->resetModel();

        return $model;
    }

    /**
     * Update a entity in repository by id.
     *
     * @param array $attributes
     * @param       $id
     *
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        $this->applyQueryScope();

        $model = $this->model->findOrFail($id);
        $model->fill($attributes);
        $model->save();

        $this->resetModel();

        event(new RepositoryEntityUpdated($this, $model));

        return $model;
    }

    /**
     * Update or Create an entity in repository.
     *
     * @param array $attributes
     * @param array $values
     *
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $this->applyQueryScope();

        $model = $this->model->updateOrCreate($attributes, $values);

        $this->resetModel();

        event(new RepositoryEntityUpdated($this, $model));

        return $model;
    }

    /**
     * Delete a entity in repository by id.
     *
     * @param $id
     *
     * @return int
     */
    public function delete($id)
    {
        $this->applyQueryScope();

        $model = $this->find($id);
        $originalModel = clone $model;

        $deleted = $model->delete();

        event(new RepositoryEntityDeleted($this, $originalModel));

        return $deleted;
    }

    /**
     * Load relations.
     *
     * @param array|string $relations
     *
     * @return self
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * Check if entity has relation.
     *
     * @param string $relation
     *
     * @return self
     */
    public function has($relation)
    {
        $this->model = $this->model->has($relation);

        return $this;
    }

    /**
     * Load relation with closure.
     *
     * @param string   $relation
     * @param \Closure $closure
     *
     * @return self
     */
    public function whereHas($relation, $closure)
    {
        $this->model = $this->model->whereHas($relation, $closure);

        return $this;
    }

    /**
     * Add subselect queries to count the relations.
     *
     * @param mixed $relations
     *
     * @return self
     */
    public function withCount($relations)
    {
        $this->model = $this->model->withCount($relations);

        return $this;
    }

    /**
     * Sync relations.
     *
     * @param $id
     * @param $relation
     * @param $attributes
     * @param bool $detaching
     *
     * @todo check if needed
     *
     * @return mixed
     */
    public function sync($id, $relation, $attributes, $detaching = true)
    {
        return $this->find($id)->{$relation}()->sync($attributes, $detaching);
    }

    /**
     * SyncWithoutDetaching.
     *
     * @param $id
     * @param $relation
     * @param $attributes
     *
     * @todo check if needed
     *
     * @return mixed
     */
    public function syncWithoutDetaching($id, $relation, $attributes)
    {
        return $this->sync($id, $relation, $attributes, false);
    }

    /**
     * Set hidden fields.
     *
     * @param array $fields
     *
     * @return self
     */
    public function hidden(array $fields)
    {
        $this->model->setHidden($fields);

        return $this;
    }

    /**
     * Set visible fields.
     *
     * @param array $fields
     *
     * @return self
     */
    public function visible(array $fields)
    {
        $this->model->setVisible($fields);

        return $this;
    }

    /**
     * Repository Query Scope Interface implementation
     */

    /**
     * Query Scope.
     *
     * @param \Closure $queryScope
     *
     * @return self
     */
    public function queryScope(Closure $queryScope)
    {
        $this->queryScope = $queryScope;

        return $this;
    }

    /**
     * Reset Query Scope.
     *
     * @return self
     */
    public function resetQueryScope()
    {
        $this->queryScope = null;

        return $this;
    }

    /*
     * Repository Scope Interface implementation
     */

    /**
     * Push Scope for filter the query.
     *
     * @param $scope
     *
     * @throws \Chelout\Repository\Exceptions\RepositoryException
     *
     * @return self
     */
    public function pushScope($scope)
    {
        if (is_string($scope)) {
            $scope = new $scope;
        }

        if (!$scope instanceof ScopeInterface) {
            throw new RepositoryException('Class ' . get_class($scope) . ' must be an instance of Chelout\\Repository\\Contracts\\ScopeInterface');
        }

        $this->scopes->push($scope);

        return $this;
    }

    /**
     * Push Scopes array for filter the query.
     *
     * @param $scopes
     *
     * @return self
     */
    public function pushScopes($scopes)
    {
        foreach ($scopes as $scope) {
            $this->pushScope($scope);
        }

        return $this;
    }

    /**
     * Pop Scope.
     *
     * @param $scope
     *
     * @return self
     */
    public function popScope($scope)
    {
        $this->scopes = $this->scopes->reject(function ($item) use ($scope) {
            if (is_object($item) && is_string($scope)) {
                return get_class($item) === $scope;
            }

            if (is_string($item) && is_object($scope)) {
                return $item === get_class($scope);
            }

            return get_class($item) === get_class($scope);
        });

        return $this;
    }

    /**
     * Get Collection of Scope.
     *
     * @return Collection
     */
    public function getScopes()
    {
        return $this->scopes;
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
        $this->model = $scope->apply($this->model, $this);

        $results = $this->model->get();

        $this->resetModel();

        return $results;
    }

    /**
     * Skip Scope.
     *
     * @param bool $status
     *
     * @return self
     */
    public function skipScope($status = true)
    {
        $this->skipScope = $status;

        return $this;
    }

    /**
     * Reset all Scopes.
     *
     * @return self
     */
    public function resetScopes()
    {
        $this->scopes = collect();

        return $this;
    }

    /*
     * Abstract Repository additional methods implementation
     */

    /**
     * Alias of All method.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function get($columns = ['*'])
    {
        return $this->all($columns);
    }

    /**
     * Retrieve first data of repository.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $this->applyScopes();
        $this->applyQueryScope();

        $results = $this->model->first($columns);

        $this->resetModel();

        return $results;
    }

    /**
     * Delete multiple entities by given scope.
     *
     * @param array $where
     *
     * @return int
     */
    public function deleteWhere(array $where)
    {
        $this->applyQueryScope();
        $this->applyConditions($where);

        $deleted = $this->model->delete();

        event(new RepositoryEntityDeleted($this, $this->model->getModel()));

        $this->resetModel();

        return $deleted;
    }

    /**
     * Apply scope in current Query.
     *
     * @return self
     */
    protected function applyQueryScope()
    {
        if (isset($this->queryScope) && is_callable($this->queryScope)) {
            $callback = $this->queryScope;
            $this->model = $callback($this->model);
        }

        return $this;
    }

    /**
     * Apply scopes in current Query.
     *
     * @return self
     */
    protected function applyScopes()
    {
        if (true === $this->skipScope) {
            return $this;
        }

        $scopes = $this->getScopes();

        if ($scopes) {
            foreach ($scopes as $scope) {
                if ($scope instanceof ScopeInterface) {
                    $this->model = $scope->apply($this->model, $this);
                }
            }
        }

        return $this;
    }

    /**
     * Applies the given where conditions to the model.
     *
     * @param array $where
     *
     * @return void
     */
    protected function applyConditions(array $where)
    {
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                [$field, $condition, $val] = $value;
                $this->model = $this->model->where($field, $condition, $val);
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }
}
