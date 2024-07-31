<?php
/** @noinspection DuplicatedCode */
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Finder;

use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Post;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\PostCategory;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\PostTag;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\PostTagList;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Site;
use ArrayAccess\TrayDigita\Database\Helper\Expression;
use ArrayAccess\TrayDigita\Database\Result\LazyResultCriteria;
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

    /**
     * @param ?array $orderBy
     * @return array<string, string>
     */
    public function filterOrder(?array $orderBy): array
    {
        if (!$orderBy) {
            return [];
        }
        $postColumnList = $this->connection->getEntityManager()->getClassMetadata(Post::class)
            ->getColumnNames();
        $postColumnList = array_map('strtolower', $postColumnList);
        $ordersBy = [];
        foreach ($orderBy as $column => $order) {
            $column = is_string($column) ? strtolower($column) : null;
            if (!$column) {
                continue;
            }
            $order = is_string($order) ? strtoupper($order) : 'ASC';
            $order = in_array($order, ['ASC', 'DESC'], true) ? $order : 'ASC';
            if (in_array($column, $postColumnList, true)) {
                $ordersBy[$column] = $order;
            }
        }
        return $ordersBy;
    }

    /**
     * @param $postType
     * @return array<string>
     */
    public function filterPostType($postType) : array
    {
        $postType = is_array($postType) ? $postType : [$postType];
        $postType = array_filter($postType, 'is_string');
        return array_values(array_map('trim', $postType));
    }

    /**
     * @param $status
     * @return array<string>
     */
    public function filterStatus($status) : array
    {
        $status = is_array($status) ? $status : [$status];
        $status = array_filter($status, 'is_string');
        return array_values(array_map('trim', $status));
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

    /**
     * @param int|string|null $id null if empty
     * @param array|string|null $status
     * @param array|string|null $postType
     * @param int|Site|null $site
     * @return Criteria
     */
    public function findSinglePostCriteria(
        int|string|null $id,
        array|string|null $status = null,
        array|string|null $postType = null,
        int|Site|null $site = null
    ) : Criteria {
        if (func_num_args() < 4) {
            $site ??= $this->getSite();
        }
        $status = $this->filterStatus($status);
        $postType = $this->filterPostType($postType);
        $site = $site instanceof Site ? $site->getId() : $site;
        $criteria = Criteria::create()
            ->where(
                is_int($id)
                    ? Criteria::expr()->eq('id', $id)
                    : (
                        is_string($id) ? Criteria::expr()->eq('slug', $id) : Criteria::expr()->isNull('id')
                )
            )
            ->andWhere(
                Criteria::expr()->eq('site_id', $site)
            );
        if (!empty($status)) {
            $criteria->andWhere(
                Criteria::expr()->in('status', $status)
            );
        }
        if (!empty($postType)) {
            $criteria->andWhere(
                Criteria::expr()->in('type', $postType)
            );
        }
        return $criteria;
    }

    public function findById(int $id): ?Post
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param string $slug
     * @param int|Site|null $site
     * @return Post|null
     */
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
    public function findPostsCriteria(
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null $site = null
    ) : Criteria {
        if (func_num_args() < 6) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        $criteria = Criteria::create()
            ->where(
                Criteria::expr()->eq('site_id', $site)
            );
        $status = $this->filterStatus($status);
        $postType = $this->filterPostType($postType);
        if (!empty($status)) {
            $criteria->andWhere(
                Criteria::expr()->in('status', $status)
            );
        }
        if (!empty($postType)) {
            $criteria->andWhere(
                Criteria::expr()->in('type', $postType)
            );
        }
        $orderBy = $this->filterOrder($orderBy);
        $orderBy = empty($orderBy) ? ['id' => 'DESC'] : $orderBy;
        if (!empty($orderBy)) {
            $criteria->orderBy($orderBy);
        }
        if ($limit) {
            $criteria->setMaxResults($limit);
        }
        if ($offset) {
            $criteria->setFirstResult($offset);
        }
        return $criteria;
    }

    public function findPosts(
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null $site = null
    ) : LazyResultCriteria {
        if (func_num_args() < 6) {
            $site ??= $this->getSite();
        }
        $criteria = $this->findPostsCriteria($orderBy, $limit, $offset, $status, $postType, $site);
        return $this->findByCriteria($criteria);
    }

    /**
     * @param int|string|PostCategory $categoryId
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return Criteria
     */
    public function findPostsByCategoryCriteria(
        int|string|PostCategory $categoryId,
        ?array           $orderBy = null,
        ?int             $limit = null,
        ?int             $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null    $site = null
    ): Criteria {
        if (func_num_args() < 7) {
            $site ??= $this->getSite();
        }
        $categoryId = $categoryId instanceof PostCategory ? $categoryId->getId() : $categoryId;
        if (is_string($categoryId)) {
            $repository = $this->connection->getRepository(PostCategory::class);
            $category = $repository->findOneBy([
                'slug' => $categoryId,
                'site_id' => $site
            ]);
            $categoryId = $category?->getId();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        $criteria = Criteria::create()
            ->where(
                $categoryId
                    ? Criteria::expr()->eq('category_id', (string)$categoryId)
                    : Criteria::expr()->isNull('category_id')
            )
            ->andWhere(
                Criteria::expr()->eq('site_id', $site)
            );
        $status = $this->filterStatus($status);
        $postType = $this->filterPostType($postType);
        if (!empty($status)) {
            $criteria->andWhere(
                Criteria::expr()->in('status', $status)
            );
        }
        if (!empty($postType)) {
            $criteria->andWhere(
                Criteria::expr()->in('type', $postType)
            );
        }
        $orderBy = $this->filterOrder($orderBy);
        $orderBy = empty($orderBy) ? ['id' => 'DESC'] : $orderBy;
        if (!empty($orderBy)) {
            $criteria->orderBy($orderBy);
        }
        if ($limit) {
            $criteria->setMaxResults($limit);
        }
        if ($offset) {
            $criteria->setFirstResult($offset);
        }
        return $criteria;
    }

    /**
     * @param int|string|PostCategory $categoryId
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return LazyResultCriteria<Post>
     */
    public function findPostsByCategory(
        int|string|PostCategory $categoryId,
        ?array           $orderBy = null,
        ?int             $limit = null,
        ?int             $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null    $site = null
    ): LazyResultCriteria {
        if (func_num_args() < 7) {
            $site ??= $this->getSite();
        }
        $criteria = $this->findPostsByCategoryCriteria(
            $categoryId,
            $orderBy,
            $limit,
            $offset,
            $status,
            $postType,
            $site
        );
        return $this->findByCriteria($criteria);
    }

    /**
     * @param Admin|string|int $author
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return Criteria
     */
    public function findPostsByAuthorCriteria(
        Admin|string|int $author,
        ?array        $orderBy = null,
        ?int          $limit = null,
        ?int          $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null $site = null
    ): Criteria {
        $authorId = $author instanceof Admin ? $author->getId() : $author;
        if (func_num_args() < 7) {
            $site ??= $this->getSite();
        }
        if (is_string($author)) {
            $repository = $this->connection->getRepository(Admin::class);
            $author = $repository->findOneBy([
                'username' => $author,
                'site_id' => $site
            ]);
            $authorId = $author ? $author->getId() : null;
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        $criteria = Criteria::create()
            ->where(
                $author
                    ? Criteria::expr()->eq('author_id', (string)$authorId)
                    : Criteria::expr()->eq('author_id', '0')
            )
            ->andWhere(
                Criteria::expr()->eq('site_id', $site)
            );
        $status = $this->filterStatus($status);
        $postType = $this->filterPostType($postType);
        if (!empty($status)) {
            $criteria->andWhere(
                Criteria::expr()->in('status', $status)
            );
        }
        if (!empty($postType)) {
            $criteria->andWhere(
                Criteria::expr()->in('type', $postType)
            );
        }
        $orderBy = $this->filterOrder($orderBy);
        $orderBy = empty($orderBy) ? ['id' => 'DESC'] : $orderBy;
        if (!empty($orderBy)) {
            $criteria->orderBy($orderBy);
        }
        if ($limit) {
            $criteria->setMaxResults($limit);
        }
        if ($offset) {
            $criteria->setFirstResult($offset);
        }
        return $criteria;
    }

    /**
     * @param Admin|string|int $author
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return LazyResultCriteria<Post>
     */
    public function findPostsByAuthor(
        Admin|string|int $author,
        ?array        $orderBy = null,
        ?int          $limit = null,
        ?int          $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null $site = null
    ) : LazyResultCriteria {
        if (func_num_args() < 7) {
            $site ??= $this->getSite();
        }
        $criteria = $this->findPostsByAuthorCriteria($author, $orderBy, $limit, $offset, $status, $postType, $site);
        return $this->findByCriteria($criteria);
    }

    /**
     * @param PostTag|string|int $tag
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return Criteria
     */
    public function findPostsByTagCriteria(
        PostTag|string|int $tag,
        ?array        $orderBy = null,
        ?int          $limit = null,
        ?int          $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null $site = null
    ): Criteria {
        $tagId = $tag instanceof PostTag ? $tag->getId() : $tag;
        if (func_num_args() < 7) {
            $site ??= $this->getSite();
        }
        if (is_string($tag)) {
            $repository = $this->connection->getRepository(PostTag::class);
            $tag = $repository->findOneBy([
                'slug' => $tag,
                'site_id' => $site
            ]);
            $tagId = $tag ? $tag->getId() : null;
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        // find post id by post_tag_list
        $tableNamePostTagList = $this->connection->getEntityManager()->getClassMetadata(PostTagList::class)
            ->getTableName();
        $tablePostName = $this->connection->getEntityManager()->getClassMetadata(Post::class)
            ->getTableName();
        $qb = $this
            ->connection
            ->createQueryBuilder()
            ->select('post_id')
            ->from($tableNamePostTagList);
        $qb
            ->select('post_id')
            ->where(
                $tag
                    ? $qb->expr()->eq('tag_id', (string)$tagId)
                    : $qb->expr()->isNull('tag_id')
            )->join(
                'post_id',
                $tablePostName,
                'post',
                'post.id = post_id'
            )->andWhere(
                $qb->expr()->eq('post.site_id', (string)$site)
            )->groupBy('post_id');
        $status = $this->filterStatus($status);
        $postType = $this->filterPostType($postType);
        $ordersBy = $this->filterOrder($orderBy);
        foreach ($ordersBy as $column => $order) {
            $column = 'post.' . $column;
            $qb->addOrderBy($column, $order);
        }
        if (!empty($status)) {
            $qb->andWhere(
                $qb->expr()->in('post.status', $status)
            );
        }
        if (!empty($postType)) {
            $qb->andWhere(
                $qb->expr()->in('post.type', $postType)
            );
        }
        $postIds = [];
        try {
            foreach ($qb->fetchAllAssociative() as $row) {
                $postIds[] = $row['post_id'];
            }
        } catch (Exception) {
        }

        $criteria = Criteria::create()
            ->where(
                empty($postIds) ? Criteria::expr()->eq('id', 0) : Criteria::expr()->in('id', $postIds)
            )
            ->andWhere(
                Criteria::expr()->eq('site_id', $site)
            );
        if (!empty($ordersBy)) {
            $criteria->orderBy($orderBy);
        }
        if ($limit) {
            $criteria->setMaxResults($limit);
        }
        if ($offset) {
            $criteria->setFirstResult($offset);
        }
        if (!empty($status)) {
            $criteria->andWhere(
                Criteria::expr()->in('status', $status)
            );
        }
        if (!empty($postType)) {
            $criteria->andWhere(
                Criteria::expr()->in('type', $postType)
            );
        }
        return $criteria;
    }

    /**
     * @param PostTag $tag
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return LazyResultCriteria<Post>
     */
    public function findPostsByTag(
        PostTag       $tag,
        ?array        $orderBy = null,
        ?int          $limit = null,
        ?int          $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null $site = null
    ): LazyResultCriteria {
        if (func_num_args() < 7) {
            $site ??= $this->getSite();
        }
        $criteria = $this->findPostsByTagCriteria($tag, $orderBy, $limit, $offset, $status, $postType, $site);
        return $this->findByCriteria($criteria);
    }

    /**
     * @param DateTimeInterface $year
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return Criteria
     */
    public function findPostsByYearCriteria(
        DateTimeInterface $year,
        ?array            $orderBy = null,
        ?int              $limit = null,
        ?int              $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null     $site = null
    ): Criteria {
        if (func_num_args() < 2) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        $criteria = Criteria::create()
            ->where(
                $site ? Criteria::expr()->eq('site_id', $site) : Criteria::expr()->isNull('site_id')
            )
            ->andWhere(
                Criteria::expr()->eq('YEAR(created_at)', $year->format('Y'))
            );
        $status = $this->filterStatus($status);
        $postType = $this->filterPostType($postType);
        if (!empty($status)) {
            $criteria->andWhere(
                Criteria::expr()->in('status', $status)
            );
        }
        if (!empty($postType)) {
            $criteria->andWhere(
                Criteria::expr()->in('type', $postType)
            );
        }

        $orderBy = $this->filterOrder($orderBy);
        $orderBy = empty($orderBy) ? ['id' => 'DESC'] : $orderBy;
        if (!empty($orderBy)) {
            $criteria->orderBy($orderBy);
        }
        if ($limit) {
            $criteria->setMaxResults($limit);
        }
        if ($offset) {
            $criteria->setFirstResult($offset);
        }
        return $criteria;
    }

    /**
     * @param DateTimeInterface $year
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return LazyResultCriteria<Post>
     */
    public function findPostsByYear(
        DateTimeInterface $year,
        ?array            $orderBy = null,
        ?int              $limit = null,
        ?int              $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null     $site = null
    ): LazyResultCriteria {
        if (func_num_args() < 7) {
            $site ??= $this->getSite();
        }
        $criteria = $this->findPostsByYearCriteria($year, $orderBy, $limit, $offset, $status, $postType, $site);
        return $this->findByCriteria($criteria);
    }

    /**
     * @param DateTimeInterface $month
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return Criteria
     */
    public function findPostsByMonthCriteria(
        DateTimeInterface $month,
        ?array            $orderBy = null,
        ?int              $limit = null,
        ?int              $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null     $site = null
    ) : Criteria {
        if (func_num_args() < 7) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        $criteria = Criteria::create()
            ->where(
                $site ? Criteria::expr()->eq('site_id', $site) : Criteria::expr()->isNull('site_id')
            )
            ->andWhere(
                Criteria::expr()->eq('DATE_FORMAT(created_at, "%Y-%m")', $month->format('Y-m'))
            );
        $status = $this->filterStatus($status);
        $postType = $this->filterPostType($postType);
        if ($status) {
            $criteria->andWhere(
                Criteria::expr()->in('status', $status)
            );
        }
        if ($postType) {
            $criteria->andWhere(
                Criteria::expr()->in('type', $postType)
            );
        }

        $orderBy = $this->filterOrder($orderBy);
        $orderBy = empty($orderBy) ? ['id' => 'DESC'] : $orderBy;
        if (!empty($orderBy)) {
            $criteria->orderBy($orderBy);
        }
        if ($limit) {
            $criteria->setMaxResults($limit);
        }
        if ($offset) {
            $criteria->setFirstResult($offset);
        }
        return $criteria;
    }

    /**
     * @param DateTimeInterface $month
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return LazyResultCriteria<Post>
     */
    public function findPostsByMonth(
        DateTimeInterface $month,
        ?array            $orderBy = null,
        ?int              $limit = null,
        ?int              $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null     $site = null
    ): LazyResultCriteria {
        if (func_num_args() < 7) {
            $site ??= $this->getSite();
        }
        $criteria = $this->findPostsByMonthCriteria($month, $orderBy, $limit, $offset, $status, $postType, $site);
        return $this->findByCriteria($criteria);
    }

    /**
     * @param DateTimeInterface $day
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return Criteria
     */
    public function findPostsByDayCriteria(
        DateTimeInterface $day,
        ?array            $orderBy = null,
        ?int              $limit = null,
        ?int              $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null     $site = null
    ) : Criteria {
        if (func_num_args() < 7) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        $criteria = Criteria::create()
            ->where(
                $site ? Criteria::expr()->eq('site_id', $site) : Criteria::expr()->isNull('site_id')
            )
            ->andWhere(
                Criteria::expr()->eq('DATE_FORMAT(created_at, "%Y-%m-%d")', $day->format('Y-m-d'))
            );
        $status = $this->filterStatus($status);
        $postType = $this->filterPostType($postType);
        if (!empty($status)) {
            $criteria->andWhere(
                Criteria::expr()->in('status', $status)
            );
        }
        if (!empty($postType)) {
            $criteria->andWhere(
                Criteria::expr()->in('type', $postType)
            );
        }

        $orderBy = $this->filterOrder($orderBy);
        $orderBy = empty($orderBy) ? ['id' => 'DESC'] : $orderBy;
        if (!empty($orderBy)) {
            $criteria->orderBy($orderBy);
        }
        if ($limit) {
            $criteria->setMaxResults($limit);
        }
        if ($offset) {
            $criteria->setFirstResult($offset);
        }
        return $criteria;
    }

    /**
     * @param DateTimeInterface $day
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return LazyResultCriteria<Post>
     */
    public function findPostsByDay(
        DateTimeInterface $day,
        ?array            $orderBy = null,
        ?int              $limit = null,
        ?int              $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null     $site = null
    ): LazyResultCriteria {
        if (func_num_args() < 7) {
            $site ??= $this->getSite();
        }
        $criteria = $this->findPostsByDayCriteria($day, $orderBy, $limit, $offset, $status, $postType, $site);
        return $this->findByCriteria($criteria);
    }

    /**
     * @param string $searchQuery
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return Criteria
     */
    public function findPostsSearchCriteria(
        string $searchQuery,
        ?array            $orderBy = null,
        ?int              $limit = null,
        ?int              $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null     $site = null
    ): Criteria {
        if (func_num_args() < 7) {
            $site ??= $this->getSite();
        }
        $columnSearch = $this->columnSearch;
        $site = $site instanceof Site ? $site->getId() : $site;
        $criteria = Criteria::create()
            ->where(
                $site ? Criteria::expr()->eq('site_id', $site) : Criteria::expr()->isNull('site_id')
            )
            ->andWhere(
                Expression::orX(
                    Expression::eq($columnSearch, $searchQuery),
                    Expression::startsWith($columnSearch, $searchQuery),
                    Expression::endsWith($columnSearch, $searchQuery)
                )
            );
        $status = $this->filterStatus($status);
        $postType = $this->filterPostType($postType);
        if (!empty($status)) {
            $criteria->andWhere(
                Criteria::expr()->in('status', $status)
            );
        }
        if (!empty($postType)) {
            $criteria->andWhere(
                Criteria::expr()->in('type', $postType)
            );
        }
        if ($limit) {
            $criteria->setMaxResults($limit);
        }
        if ($offset) {
            $criteria->setFirstResult($offset);
        }
        $orderBy = $this->filterOrder($orderBy);
        $orderBy = empty($orderBy) ? ['id' => 'DESC'] : $orderBy;
        if (!empty($orderBy)) {
            $criteria->orderBy($orderBy);
        }
        return $criteria;
    }

    /**
     * @param string $searchQuery
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string|array|null $status
     * @param string|array|null $postType
     * @param int|Site|null $site
     * @return LazyResultCriteria<Post>
     */
    public function findPostsSearch(
        string $searchQuery,
        ?array            $orderBy = null,
        ?int              $limit = null,
        ?int              $offset = null,
        string|array|null $status = null,
        string|array|null $postType = null,
        int|Site|null     $site = null
    ): LazyResultCriteria {
        $criteria = $this->findPostsSearchCriteria($searchQuery, $orderBy, $limit, $offset, $status, $postType, $site);
        return $this->findByCriteria($criteria);
    }
}
