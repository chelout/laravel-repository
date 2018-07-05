<?php
namespace Chelout\Repository\Contracts;

/**
 * Interface ScopeInterface
 * @package Chelout\Repository\Contracts
 */
interface ScopeInterface
{
    /**
     * Apply scope in query repository
     *
     * @param                     $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository);
}
