<?php

namespace Chelout\Repository\Contracts;

use Illuminate\Support\Collection;

/**
 * Interface RepositoryScopeInterface.
 */
interface RepositoryScopeInterface
{
    /**
     * Push Scope for filter the query.
     *
     * @param $scope
     *
     * @return $this
     */
    public function pushScope($scope);

    /**
     * Push Scopes array for filter the query.
     *
     * @param $scopes
     *
     * @return self
     */
    public function pushScopes($scopes);

    /**
     * Pop Scope.
     *
     * @param $scope
     *
     * @return $this
     */
    public function popScope($scope);

    /**
     * Get Collection of Scope.
     *
     * @return Collection
     */
    public function getScopes();

    /**
     * Find data by Scope.
     *
     * @param ScopeInterface $scope
     *
     * @return mixed
     */
    public function getByScope(ScopeInterface $scope);

    /**
     * Skip Scope.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skipScope($status = true);

    /**
     * Reset all Scopes.
     *
     * @return $this
     */
    public function resetScopes();
}
