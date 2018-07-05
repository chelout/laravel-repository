<?php

namespace Chelout\Repository\Contracts;

/**
 * Interface RepositoryInterface.
 */
interface RepositoryInterface
{
    /**
     * Find data by id.
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * Find data by field and value.
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findByField($field, $value, $columns = ['*']);

    /**
     * Find data by multiple fields.
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhere(array $where, $columns = ['*']);

    /**
     * Find data by multiple values in one field.
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhereIn($field, array $values, $columns = ['*']);

    /**
     * Find data by excluding multiple values in one field.
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhereNotIn($field, array $values, $columns = ['*']);

    /**
     * Retrieve all data of repository.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*']);

    /**
     * Retrieve data array for populate field select.
     *
     * @param string      $column
     * @param string|null $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function pluck($column, $key = null);

    /**
     * Retrieve all data of repository, paginated.
     *
     * @param null  $limit
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*']);

    /**
     * Retrieve all data of repository, simple paginated.
     *
     * @param null  $limit
     * @param array $columns
     *
     * @return mixed
     */
    public function simplePaginate($limit = null, $columns = ['*']);

    /**
     * Order collection by a given column.
     *
     * @param string $column
     * @param string $direction
     *
     * @return self
     */
    public function orderBy($column, $direction = 'asc');

    /**
     * Save a new entity in repository.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes);

    /**
     * Retrieve first data of repository, or return new Entity.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function firstOrNew(array $attributes = []);

    /**
     * Retrieve first data of repository, or create new Entity.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function firstOrCreate(array $attributes = []);

    /**
     * Update a entity in repository by id.
     *
     * @param array $attributes
     * @param       $id
     *
     * @return mixed
     */
    public function update(array $attributes, $id);

    /**
     * Update or Create an entity in repository.
     *
     * @param array $attributes
     * @param array $values
     *
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values = []);

    /**
     * Delete a entity in repository by id.
     *
     * @param $id
     *
     * @return int
     */
    public function delete($id);

    /**
     * Load relations.
     *
     * @param $relations
     *
     * @return self
     */
    public function with($relations);

    /**
     * Load relation with closure.
     *
     * @param string  $relation
     * @param closure $closure
     *
     * @return self
     */
    public function whereHas($relation, $closure);

    /**
     * Add subselect queries to count the relations.
     *
     * @param mixed $relations
     *
     * @return self
     */
    public function withCount($relations);

    /**
     * Sync relations.
     *
     * @param $id
     * @param $relation
     * @param $attributes
     * @param bool $detaching
     *
     * @return mixed
     */
    public function sync($id, $relation, $attributes, $detaching = true);

    /**
     * SyncWithoutDetaching.
     *
     * @param $id
     * @param $relation
     * @param $attributes
     *
     * @return mixed
     */
    public function syncWithoutDetaching($id, $relation, $attributes);

    /**
     * Set hidden fields.
     *
     * @param array $fields
     *
     * @return self
     */
    public function hidden(array $fields);

    /**
     * Set visible fields.
     *
     * @param array $fields
     *
     * @return self
     */
    public function visible(array $fields);
}
