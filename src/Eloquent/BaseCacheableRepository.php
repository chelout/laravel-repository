<?php

namespace Chelout\Repository\Eloquent;

use App\Post;
use Chelout\Repository\Contracts\CacheableInterface;
use Chelout\Repository\Traits\CacheableRepository;
use Chelout\Repository\Eloquent\BaseRepository;

class BaseCacheableRepository implements CacheableInterface
{
    use CacheableRepository;
}
