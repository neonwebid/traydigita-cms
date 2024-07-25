<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Static;

use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Post;
use Doctrine\Common\Collections\Criteria;

final class PostStatic
{
    public static function findBySlug(string $slug): ?Post
    {
        return CoreModuleStatic::core()?->finder->post->findBySlug($slug);
    }

    public static function findById(int $id): ?Post
    {
        return CoreModuleStatic::core()?->finder->post->findById($id);
    }

    public static function findByTitle(string $title): ?Post
    {
        $finder = CoreModuleStatic::core()?->finder->post;
        $site = $finder?->getSite()?->getId();
        return $finder?->findByCriteria(Criteria::create()->where(
            Criteria::expr()->eq('title', $title)
        )->andWhere(
            $site !== null ? Criteria::expr()->eq('site', $site) : Criteria::expr()->isNull('site')
        ))->current();
    }
}
