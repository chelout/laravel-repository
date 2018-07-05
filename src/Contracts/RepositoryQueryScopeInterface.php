<?php

namespace Chelout\Repository\Contracts;

use Illuminate\Support\Collection;

/**
 * Interface RepositoryScopeInterface.
 */
interface RepositoryQueryScopeInterface
{
    /**
     * Query Scope.
     *
     * @param \Closure $queryScope
     *
     * @return self
     */
    public function queryScope(\Closure $queryScope);

    /**
     * Reset Query Scope.
     *
     * @return self
     */
    public function resetQueryScope();
}
