<?php
namespace Chelout\Repository\Events;

/**
 * Class RepositoryEntityCreated
 * @package Chelout\Repository\Events
 */
class RepositoryEntityCreated extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "created";
}
