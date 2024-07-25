<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Depends;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Post as PostEntity;
use ArrayAccess\TrayDigita\Database\Entities\Interfaces\AvailabilityStatusEntityInterface;
use ArrayAccess\TrayDigita\Exceptions\InvalidArgument\InvalidArgumentException;
use DateTimeInterface;
use Doctrine\Common\Collections\Criteria;
use SplObjectStorage;

class PostLoop
{
    public const MAX_RESULTS = 1000;

    /* -----------------------------------------------------------------
     * Constants Of MODE
     * -----------------------------------------------------------------
     */
    public const MODE_HOMEPAGE = 'homepage';

    public const MODE_SINGLE = 'single';

    public const MODE_SEARCH = 'search';

    public const MODE_CATEGORY = 'category';

    public const MODE_AUTHOR = 'author';

    public const MODE_TAG = 'tag';

    public const MODE_YEAR = 'year';

    public const MODE_MONTH = 'month';

    public const MODE_DAY = 'day';

    public const MODE_NOT_FOUND = '404';

    /**
     * @var array|string[] AVAILABLE_MODES
     */
    public const AVAILABLE_MODES = [
        self::MODE_HOMEPAGE,
        self::MODE_SINGLE,
        self::MODE_SEARCH,
        self::MODE_CATEGORY,
        self::MODE_AUTHOR,
        self::MODE_NOT_FOUND,
        self::MODE_YEAR,
        self::MODE_MONTH,
        self::MODE_DAY,
        self::MODE_TAG
    ];

    /**
     * @var array|string[] ALLOW_ORDER_BY List of allowed order by
     */
    public const ALLOW_ORDER_BY = [
        'id',
        'title',
        'slug',
        'published_at',
        'created_at',
        'updated_at',
        'status'
    ];

    /**
     * @var int $perPage
     */
    protected int $perPage = 10;

    /**
     * @var int $page
     */
    protected int $page = 1;

    /**
     * @var array|string[] $orderBy
     */
    protected array $orderBy = [
        'published_at' => 'DESC',
        'id' => 'DESC'
    ];

    /**
     * @var array|string[] $status
     */
    protected array $status = [
        AvailabilityStatusEntityInterface::PUBLISHED
    ];

    /**
     * @var string $postType
     */
    protected string $postType = PostEntity::TYPE_POST;

    /**
     * @var string $mode
     */
    protected string $mode = self::MODE_NOT_FOUND;

    /**
     * @var PostEntity|null $post
     */
    protected ?PostEntity $post = null;

    /**
     * @var string|int|null $postIdOrSlug
     */
    protected string|int|null $postIdOrSlug = null;

    /**
     * @var string|int|null $authorIdOrSlug
     */
    protected string|int|null $authorIdOrSlug = null;

    /**
     * @var DateTimeInterface|null $dateTime
     */
    protected ?DateTimeInterface $dateTime = null;

    /**
     * @var string|int|null $categoryIdOrSlug
     */
    protected string|int|null $categoryIdOrSlug = null;

    /**
     * @var string|int|null $tagIdOrSlug
     */
    protected string|int|null $tagIdOrSlug = null;

    /**
     * @var string|null $search
     */
    protected ?string $search = null;

    /**
     * @var bool|null $found
     */
    protected ?bool $found = null;

    /**
     * @var int $currentPostIndex
     */
    protected int $currentPostIndex = 0;

    /**
     * @var int $totalPosts
     */
    protected int $totalPosts = 0;

    /**
     * @var bool $inTheLoop
     */
    protected bool $inTheLoop = false;

    public function __construct(
        public readonly Core $core
    ) {
    }

    public function isValidMode(string $mode): bool
    {
        return in_array($mode, self::AVAILABLE_MODES, true);
    }

    public function isValidOrderBy(string $orderBy): bool
    {
        return in_array($orderBy, self::ALLOW_ORDER_BY, true);
    }

    public function getDateTime(): ?DateTimeInterface
    {
        return $this->dateTime;
    }

    /**
     * @param ?DateTimeInterface $dateTime
     * @return void
     */
    public function setDateTime(?DateTimeInterface $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function getPostIdOrSlug(): string|int|null
    {
        return $this->postIdOrSlug;
    }

    public function setPostIdOrSlug(string|int $postIdOrSlug): void
    {
        if ($this->inTheLoop) {
            throw new InvalidArgumentException('Cannot change id or slug after post is initialized');
        }
        $this->postIdOrSlug = $postIdOrSlug;
    }

    public function getAuthorIdOrSlug(): string|int|null
    {
        return $this->authorIdOrSlug;
    }

    public function setAuthorIdOrSlug(string|int $authorIdOrSlug): void
    {
        if ($this->inTheLoop) {
            throw new InvalidArgumentException('Cannot change author id or slug after post is initialized');
        }
        $this->authorIdOrSlug = $authorIdOrSlug;
    }

    public function getCategoryIdOrSlug(): string|int|null
    {
        return $this->categoryIdOrSlug;
    }

    public function setCategoryIdOrSlug(string|int $categoryIdOrSlug): void
    {
        if ($this->inTheLoop) {
            throw new InvalidArgumentException('Cannot change category id or slug after post is initialized');
        }
        $this->categoryIdOrSlug = $categoryIdOrSlug;
    }

    public function getTagIdOrSlug(): string|int|null
    {
        return $this->tagIdOrSlug;
    }

    public function setTagIdOrSlug(string|int $tagIdOrSlug): void
    {
        if ($this->inTheLoop) {
            throw new InvalidArgumentException('Cannot change tag id or slug after post is initialized');
        }
        $this->tagIdOrSlug = $tagIdOrSlug;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): void
    {
        if ($this->inTheLoop) {
            throw new InvalidArgumentException('Cannot change search after post is initialized');
        }
        $this->search = $search;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return void
     * @see self::AVAILABLE_MODES
     */
    public function setMode(string $mode): void
    {
        if ($this->inTheLoop) {
            throw new InvalidArgumentException('Cannot change mode after post is initialized');
        }
        if (!$this->isValidMode($mode)) {
            throw new InvalidArgumentException('Invalid mode');
        }
        $this->mode = $mode;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        if ($this->inTheLoop) {
            throw new InvalidArgumentException('Cannot change per page after loop is started');
        }
        $page = max(1, $page);
        $this->page = $page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setPerPage(int $perPage): void
    {
        if ($this->inTheLoop) {
            throw new InvalidArgumentException('Cannot change per page after loop is started');
        }
        $perPage = max(1, $perPage); // minimum 1
        $perPage = min($perPage, self::MAX_RESULTS); // maximum 1000
        $this->perPage = $perPage;
    }

    public function setOrderBy(array $orderBy): void
    {
        if ($this->inTheLoop) {
            throw new InvalidArgumentException('Cannot change per page after loop is started');
        }
        $newOrderBy = [];
        foreach ($orderBy as $key => $value) {
            if (!is_string($key) || !is_string($value)) {
                throw new InvalidArgumentException('Invalid order by key or value');
            }
            $key = strtolower(trim($key));
            $value = strtoupper(trim($value));
            if (!$this->isValidOrderBy($key)) {
                throw new InvalidArgumentException('Invalid order by key');
            }
            if (!in_array($value, ['ASC', 'DESC', 'RANDOM'], true)) {
                throw new InvalidArgumentException('Invalid order by value');
            }
            $newOrderBy[$key] = strtoupper($value);
        }
        $this->orderBy = $newOrderBy;
    }

    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array|string $status): void
    {
        if ($this->inTheLoop) {
            throw new InvalidArgumentException('Cannot change per page after loop is started');
        }
        $status = is_string($status) ? [$status] : $status;
        $status = array_values($status);
        foreach ($status as $key => $value) {
            if (!is_string($value)) {
                throw new InvalidArgumentException('Invalid status value');
            }
            $status[$key] = trim($value);
        }
        $status = array_filter($status);
        $status = array_unique($status);
        $this->status = empty($status) ? [AvailabilityStatusEntityInterface::PUBLISHED] : $status;
    }

    public function getPostType(): string
    {
        return $this->postType;
    }

    public function setPostType(string $postType): void
    {
        if ($this->inTheLoop) {
            throw new InvalidArgumentException('Cannot change per page after loop is started');
        }
        $this->postType = trim($postType) ?: PostEntity::TYPE_POST;
    }

    public function isHomepage(): bool
    {
        return $this->mode === self::MODE_HOMEPAGE;
    }

    /**
     * @return bool
     */
    public function isFound(): bool
    {
        return $this->found === true;
    }

    public function isDate(): bool
    {
        if ($this->dateTime === null || $this->posts === null) {
            return false;
        }
        return $this->isYear() || $this->isMonth() || $this->isDay();
    }

    public function isYear(): bool
    {
        return $this->mode === self::MODE_YEAR && $this->dateTime !== null && $this->posts !== null;
    }

    public function isMonth(): bool
    {
        return $this->mode === self::MODE_MONTH && $this->dateTime !== null && $this->posts !== null;
    }

    public function isDay(): bool
    {
        return $this->mode === self::MODE_DAY && $this->dateTime !== null && $this->posts !== null;
    }

    public function isCategory(): bool
    {
        if (!$this->isFound()) {
            return false;
        }
        return $this->mode === self::MODE_CATEGORY && $this->categoryIdOrSlug !== null && $this->posts !== null;
    }

    public function isSearch(): bool
    {
        return $this->mode === self::MODE_SEARCH && $this->search !== null && $this->posts !== null;
    }

    public function isTag(): bool
    {
        if ($this->isFound() === false) {
            return false;
        }
        return $this->mode === self::MODE_TAG && $this->tagIdOrSlug !== null && $this->posts !== null;
    }

    public function isAuthor(): bool
    {
        if ($this->isFound() === false) {
            return false;
        }
        return $this->mode === self::MODE_AUTHOR && $this->authorIdOrSlug !== null && $this->posts !== null;
    }

    public function isArchive(): bool
    {
        return $this->isDate() || $this->isCategory() || $this->isTag() || $this->isAuthor();
    }

    public function isSingular(?string $postType = null): bool
    {
        if (!$this->isFound()) {
            return false;
        }
        $result = $this->mode === self::MODE_SINGLE
            && $this->postIdOrSlug !== null;
        if (!$result) {
            return false;
        }
        return $postType === null || $this->postType === $postType;
    }

    public function isPage(): bool
    {
        return $this->isSingular(PostEntity::TYPE_PAGE);
    }

    public function isSingle(): bool
    {
        return $this->isSingular(PostEntity::TYPE_POST);
    }

    public function is404(): bool
    {
        // if mode is not found or found is false
        if ($this->mode === self::MODE_NOT_FOUND || !$this->isFound()) {
            return true;
        }

        return !$this->isArchive() && !$this->isSingular() && !$this->isHomepage();
    }

    public function isHome(): bool
    {
        return $this->isHomepage();
    }

    public function isFrontPage(): bool
    {
        return $this->isHomepage() && $this->page === 1;
    }

    public function isInTheLoop(): bool
    {
        return $this->inTheLoop;
    }

    public function isPaged(): bool
    {
        return $this->page > 1 && !$this->is404() && !$this->isSingular();
    }

    /**
     * Reset the loop
     *
     * @return void
     */
    public function reset(): void
    {
        if (!$this->isInTheLoop() && $this->posts === null) {
            return;
        }
        $this->found = null;
        $this->post = null;
        $this->posts = null;
        $this->currentPostIndex = 0;
        $this->totalPosts = 0;
        $this->inTheLoop = false;
    }

    public function rewindPosts(): void
    {
        if ($this->posts === null) {
            return;
        }
        $this->posts->rewind();
        $this->currentPostIndex = -1;
        if ($this->totalPosts > 0) {
            $this->post = $this->posts->current();
        }
    }

    public function thePost(): ?PostEntity
    {
        if ($this->posts === null || $this->found === false) {
            return null;
        }
        if ($this->currentPostIndex === -1) {
            $this->core->getManager()->dispatch(
                'post.loopStart',
                $this
            );
        }

        $this->inTheLoop = true;
        $this->post = $this->nextPost();
        return $this->post;
    }

    public function getPost(): ?PostEntity
    {
        return $this->post;
    }

    public function nextPost(): ?PostEntity
    {
        if ($this->posts === null || $this->found === false) {
            return null;
        }
        if ($this->currentPostIndex >= $this->totalPosts) {
            return null;
        }
        $this->currentPostIndex++;
        if ($this->currentPostIndex === 0) {
            $this->posts->rewind();
        } else {
            $this->posts->next();
        }
        $this->post = $this->posts->current();
        return $this->post;
    }

    /**
     * Check if there are posts
     *
     */
    public function havePosts() : bool
    {
        if ($this->posts === null) {
            $this->initPosts();
        }
        if ($this->isFound() === false) {
            $this->inTheLoop = false;
            return false;
        }
        if ($this->currentPostIndex + 1 < $this->totalPosts) {
            return true;
        }
        if ($this->currentPostIndex + 1 === $this->totalPosts && $this->totalPosts > 0) {
            // Do some cleaning up after the loop.
            $this->rewindPosts();
        }
        $this->inTheLoop = false;
        return false;
    }

    /**
     * @return ?SplObjectStorage<PostEntity>
     */
    private ?SplObjectStorage $posts = null;

    private function initPosts(): void
    {
        $site = $this->core->getSite()->current()?->getId();
        $criteria = Criteria::create()
            ->where(
                $site ? Criteria::expr()->eq('site_id', $site) : Criteria::expr()->isNull('site_id')
            )->andWhere(
                Criteria::expr()->in('status', $this->getStatus())
            )->andWhere(
                Criteria::expr()->eq('post_type', $this->postType)
            )->setMaxResults($this->perPage)
            ->setFirstResult(($this->page - 1) * $this->perPage)
            ->orderBy($this->orderBy);

        // todo implement another way to get posts
        // ...

        $this->posts = new SplObjectStorage();
        foreach ($this->core->finder->post->findByCriteria(
            $criteria
        ) as $post) {
            $this->posts->attach($post);
        }
        $this->totalPosts = $this->posts->count();
        $this->found = $this->totalPosts > 0;
        $this->currentPostIndex = -1;
        $this->rewindPosts();
    }
}
