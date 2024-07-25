<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Depends;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Post as PostEntity;
use ArrayAccess\TrayDigita\Database\Entities\Interfaces\AvailabilityStatusEntityInterface;
use ArrayAccess\TrayDigita\Exceptions\InvalidArgument\InvalidArgumentException;
use DateTimeInterface;

class PostLoop
{
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
     * @var bool $initializePost
     */
    protected bool $initializePost = false;

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
        if ($this->initializePost) {
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
        if ($this->initializePost) {
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
        if ($this->initializePost) {
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
        if ($this->initializePost) {
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
        if ($this->initializePost) {
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
     * @see self::AVAILABLE_MODES
     * @return void
     */
    public function setMode(string $mode): void
    {
        if ($this->initializePost) {
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
        if ($this->initializePost) {
            throw new InvalidArgumentException('Cannot change page after post is initialized');
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
        if ($this->initializePost) {
            throw new InvalidArgumentException('Cannot change max result after post is initialized');
        }
        $perPage = max(1, $perPage);
        $this->perPage = $perPage;
    }

    public function setOrderBy(array $orderBy): void
    {
        if ($this->initializePost) {
            throw new InvalidArgumentException('Cannot change order by after post is initialized');
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
        if ($this->initializePost) {
            throw new InvalidArgumentException('Cannot change status after post is initialized');
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
        if ($this->initializePost) {
            throw new InvalidArgumentException('Cannot change post type after post is initialized');
        }
        $this->postType = trim($postType) ?: PostEntity::TYPE_POST;
    }

    public function isHomepage(): bool
    {
        return $this->mode === self::MODE_HOMEPAGE;
    }

    /**
     * @return bool|null
     */
    public function isFound() : ?bool
    {
        return $this->found;
    }

    public function isDate(): bool
    {
        if ($this->dateTime === null) {
            return false;
        }
        return $this->isYear() || $this->isMonth() || $this->isDay();
    }

    public function isYear(): bool
    {
        return $this->mode === self::MODE_YEAR && $this->dateTime !== null;
    }

    public function isMonth(): bool
    {
        return $this->mode === self::MODE_MONTH && $this->dateTime !== null;
    }

    public function isDay(): bool
    {
        return $this->mode === self::MODE_DAY && $this->dateTime !== null;
    }

    public function isCategory(): bool
    {
        if ($this->found === false) {
            return false;
        }
        return $this->mode === self::MODE_CATEGORY && $this->categoryIdOrSlug !== null;
    }

    public function isSearch(): bool
    {
        return $this->mode === self::MODE_SEARCH && $this->search !== null;
    }

    public function isTag(): bool
    {
        if ($this->found === false) {
            return false;
        }
        return $this->mode === self::MODE_TAG && $this->tagIdOrSlug !== null;
    }

    public function isAuthor(): bool
    {
        if ($this->found === false) {
            return false;
        }
        return $this->mode === self::MODE_AUTHOR && $this->authorIdOrSlug !== null;
    }

    public function isArchive(): bool
    {
        return $this->isDate() || $this->isCategory() || $this->isTag() || $this->isAuthor();
    }

    public function isSingular(?string $postType = null): bool
    {
        if ($this->found === false) {
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
        if ($this->mode === self::MODE_NOT_FOUND) {
            return true;
        }
        if ($this->found === false) {
            return true;
        }

        return !$this->isArchive() && !$this->isSingular() && !$this->isHomepage();
    }

    public function havePosts()
    {
        if ($this->initializePost) {
            return $this->post !== null;
        }

        $this->initializePost = true;
        switch ($this->mode) {
            case self::MODE_NOT_FOUND:
                $this->post = null;
                break;
        }
        // todo add more cases
    }

    public function thePost(): ?PostEntity
    {
        return $this->post;
    }
}
