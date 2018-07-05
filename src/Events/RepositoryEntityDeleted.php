<?php
namespace Chelout\Repository\Events;

/**
 * Class RepositoryEntityDeleted
 * @package Chelout\Repository\Events
 */
class RepositoryEntityDeleted extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "deleted";
}
