<?php
namespace Chelout\Repository\Events;

/**
 * Class RepositoryEntityUpdated
 * @package Chelout\Repository\Events
 */
class RepositoryEntityUpdated extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "updated";
}
