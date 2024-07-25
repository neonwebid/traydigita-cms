<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Finder;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;

/**
 * @property-read UserFinder $user
 * @property-read AdminFinder $admin
 * @property-read PostFinder $post
 * @property-read CategoryFinder $category
 * @property-read RoleFinder $role
 */
final class FinderCollector
{
    /**
     * @var array<class-string<AbstractFinder>, AbstractFinder>
     */
    protected array $finder = [];

    public function __construct(public readonly Core $core)
    {
    }

    public function getUser() : UserFinder
    {
        return $this->finder[UserFinder::class] ??= new UserFinder($this->core);
    }

    public function getAdmin() : AdminFinder
    {
        return $this->finder[AdminFinder::class] ??= new AdminFinder($this->core);
    }

    public function getPost() : PostFinder
    {
        return $this->finder[PostFinder::class] ??= new PostFinder($this->core);
    }

    public function getCategory() : CategoryFinder
    {
        return $this->finder[CategoryFinder::class] ??= new CategoryFinder($this->core);
    }
    public function getRole() : RoleFinder
    {
        return $this->finder[RoleFinder::class] ??= new RoleFinder($this->core);
    }

    public function __get(string $name) : ?AbstractFinder
    {
        return match ($name) {
            'user' => $this->getUser(),
            'admin' => $this->getAdmin(),
            'post' => $this->getPost(),
            'category' => $this->getCategory(),
            'role' => $this->getRole(),
            default => null,
        };
    }
}
