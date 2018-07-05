<?php

namespace Chelout\Repository\Listeners;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Chelout\Repository\Contracts\RepositoryInterface;
use Chelout\Repository\Events\RepositoryEventBase;

/**
 * Class CleanCacheRepository
 * @package Chelout\Repository\Listeners
 */
class CleanCacheRepository
{

    /**
     * @var CacheRepository
     */
    protected $cache = null;

    /**
     * @var RepositoryInterface
     */
    protected $repository = null;

    /**
     * @var Model
     */
    protected $model = null;

    /**
     * @var string
     */
    protected $action = null;

    /**
     *
     */
    public function __construct()
    {
        $this->cache = app(
            config('repository.cache.repository', 'cache')
        );
    }

    /**
     * @param RepositoryEventBase $event
     */
    public function handle(RepositoryEventBase $event)
    {
        $cleanEnabled = config("repository.cache.clean.enabled", true);

        if (!$cleanEnabled) {
            return false;
        }

        $this->repository = $event->getRepository();
        $this->model = $event->getModel();
        $this->action = $event->getAction();

        if (config("repository.cache.clean.on.{$this->action}", true)) {
            $this->cache->tags(
                $this->repository->getCacheTags()
            )->flush();
        }
    }
}
