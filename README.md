# Laravel Repository

## Usage

```php
class PostRepository extends BaseRepository
{
    protected $fqcn = Post::class;
}
```

```php
class CacheablePostRepository extends BaseCacheableRepository
{
    protected $fqcn = Post::class;

    public $repository;

    public function __construct(PostRepository $repository) {
        $this->repository = $repository;
    }
}
```