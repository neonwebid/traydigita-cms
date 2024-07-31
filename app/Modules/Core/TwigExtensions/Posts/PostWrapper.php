<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\TwigExtensions\Posts;

use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Post;

/**
 * Manipulate {{the_post()}} object
 *
 * @mixin Post
 */
class PostWrapper
{
    public function __construct(public Post $post)
    {
    }

    /**
     * @param $post
     * @return PostWrapper|null
     */
    public static function create($post): ?PostWrapper
    {
        return $post instanceof Post ? new self($post) : null;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->post->{$name}(...$arguments);
    }

    public function __get(string $name)
    {
        return $this->post->{$name};
    }

    public function __set(string $name, mixed $value)
    {
        $this->post->{$name} = $value;
    }

    public function __toString(): string
    {
        return '';
    }
}
