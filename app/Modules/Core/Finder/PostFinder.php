<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Finder;

use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Post;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\PostCategory;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\PostTag;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\PostTagList;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Site;
use DateTimeInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ObjectRepository;
use function is_int;
use function is_string;

class PostFinder extends AbstractFinder
{
    protected ?string $columnSearch = 'title';

    /**
     * @return ObjectRepository&Selectable<Post>
     */
    public function getRepository(): ObjectRepository&Selectable
    {
        return $this->connection->getRepository(
            Post::class
        );
    }

    public function find($id, int|Site|null $site = null): ?Post
    {
        if (is_int($id)) {
            return $this->findById($id);
        }
        if (is_string($id)) {
            return $this->findBySlug($id, $site);
        }
        return null;
    }

    public function findById(int $id): ?Post
    {
        return $this->getRepository()->find($id);
    }

    public function findBySlug(string $slug, int|Site|null $site = null): ?Post
    {
        if (func_num_args() < 2) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        return $this
            ->getRepository()
            ->findOneBy([
                'slug' => $slug,
                'site_id' => $site
            ]);
    }

    public function findPostByCategory(
        int|PostCategory $categoryId,
        ?array           $orderBy = null,
        ?int             $limit = null,
        ?int             $offset = null,
        ?string          $status = null,
        ?string          $postType = null,
        int|Site|null    $site = null
    ): array {
        $categoryId = $categoryId instanceof PostCategory ? $categoryId->getId() : $categoryId;
        if (func_num_args() < 2) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        $args = [
            'category_id' => $categoryId,
            'site_id' => $site
        ];
        if ($status) {
            $args['status'] = $status;
        }
        if ($postType) {
            $args['post_type'] = $postType;
        }
        return $this
            ->getRepository()
            ->findBy($args, $orderBy, $limit, $offset);
    }

    /**
     * @param PostTag $tag
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|null $status
     * @param int|Site|null $site
     * @return ?Criteria
     */
    public function findPostByTagCriteria(
        PostTag       $tag,
        ?array        $orderBy = null,
        ?int          $limit = null,
        ?int          $offset = null,
        ?string       $status = null,
        ?string       $postType = null,
        int|Site|null $site = null
    ): ?Criteria {
        if (func_num_args() < 2) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        // find post id by post_tag_list
        $tableNamePostTagList = $this->connection->getEntityManager()->getClassMetadata(PostTagList::class)
            ->getTableName();
        $qb = $this
            ->connection
            ->createQueryBuilder()
            ->select('post_id')
            ->from($tableNamePostTagList);
        $qb
            ->where(
                $qb->expr()->eq('tag_id', (string)$tag->getId())
            );
        $postIds = [];
        try {
            foreach ($qb->fetchAllAssociative() as $row) {
                $postIds[] = $row['post_id'];
            }
        } catch (Exception) {
            return null;
        }
        $criteria = Criteria::create()
            ->where(
                Criteria::expr()->in('id', $postIds)
            )
            ->andWhere(
                Criteria::expr()->eq('site_id', $site)
            );
        if ($orderBy) {
            $criteria->orderBy($orderBy);
        }
        if ($limit) {
            $criteria->setMaxResults($limit);
        }
        if ($offset) {
            $criteria->setFirstResult($offset);
        }
        if ($status) {
            $criteria->andWhere(
                Criteria::expr()->eq('status', $status)
            );
        }
        if ($postType) {
            $criteria->andWhere(
                Criteria::expr()->eq('post_type', $postType)
            );
        }
        return $criteria;
    }

    public function findPostByTag(
        PostTag       $tag,
        ?array        $orderBy = null,
        ?int          $limit = null,
        ?int          $offset = null,
        ?string       $status = null,
        ?string       $postType = null,
        int|Site|null $site = null
    ): iterable {
        $criteria = $this->findPostByTagCriteria($tag, $orderBy, $limit, $offset, $status, $postType, $site);
        if (!$criteria) {
            return [];
        }
        return $this
            ->getRepository()
            ->matching(
                $criteria
            );
    }

    public function findPostByYear(
        DateTimeInterface $year,
        ?array            $orderBy = null,
        ?int              $limit = null,
        ?int              $offset = null,
        ?string           $status = null,
        ?string           $postType = null,
        int|Site|null     $site = null
    ): array {
        if (func_num_args() < 2) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        $args = [
            'site_id' => $site,
            'YEAR(created_at)' => $year->format('Y')
        ];
        if ($status) {
            $args['status'] = $status;
        }
        if ($postType) {
            $args['post_type'] = $postType;
        }
        return $this
            ->getRepository()
            ->findBy($args, $orderBy, $limit, $offset);
    }

    public function findPostByMonth(
        DateTimeInterface $month,
        ?array            $orderBy = null,
        ?int              $limit = null,
        ?int              $offset = null,
        ?string           $status = null,
        ?string           $postType = null,
        int|Site|null     $site = null
    ): array {
        if (func_num_args() < 2) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        $month = $month->format('Y-m');
        $args = [
            'site_id' => $site,
            'DATE_FORMAT(created_at, "%Y-%m")' => $month
        ];
        if ($status) {
            $args['status'] = $status;
        }
        if ($postType) {
            $args['post_type'] = $postType;
        }
        return $this
            ->getRepository()
            ->findBy($args, $orderBy, $limit, $offset);
    }

    public function findPostByDay(
        DateTimeInterface $day,
        ?array            $orderBy = null,
        ?int              $limit = null,
        ?int              $offset = null,
        ?string           $status = null,
        ?string           $postType = null,
        int|Site|null     $site = null
    ): array {
        if (func_num_args() < 2) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        $day = $day->format('Y-m-d');
        $args = [
            'site_id' => $site,
            'DATE_FORMAT(created_at, "%Y-%m-%d")' => $day
        ];
        if ($status) {
            $args['status'] = $status;
        }
        if ($postType) {
            $args['post_type'] = $postType;
        }
        return $this
            ->getRepository()
            ->findBy($args, $orderBy, $limit, $offset);
    }
}
