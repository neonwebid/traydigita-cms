<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Entities;

use ArrayAccess\TrayDigita\Collection\Collection;
use ArrayAccess\TrayDigita\Database\Entities\Abstracts\AbstractEntity;
use ArrayAccess\TrayDigita\Database\Helper\Expression;
use ArrayAccess\TrayDigita\Util\Generator\UUID;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @property-read int $id
 * @property-read ?int $site_id
 * @property-read string $name
 * @property-read ?string $description
 * @property-read string $slug
 * @property-read ?int $user_id
 * @property-read DateTimeInterface $created_at
 * @property-read DateTimeInterface $updated_at
 * @property-read ?Admin $user
 * @property-read ?Site $site
 * @property-read ?Collection $tag_list
 */
#[Entity]
#[Table(
    name: self::TABLE_NAME,
    options: [
        'charset' => 'utf8mb4', // remove this or change to utf8 if not use mysql
        'collation' => 'utf8mb4_unicode_ci',  // remove this if not use mysql
        'comment' => 'Post tag metadata',
        'priority' => 99,
    ]
)]
#[UniqueConstraint(
    name: 'unique_slug_site_id',
    columns: ['slug', 'site_id']
)]
#[Index(
    name: 'index_id_site_id',
    columns: ['id', 'site_id']
)]
#[Index(
    name: 'index_name_site_id',
    columns: ['name', 'site_id']
)]
#[Index(
    name: 'relation_post_tags_site_id_sites_id',
    columns: ['site_id']
)]
#[Index(
    name: 'relation_post_tags_user_id_admins_id',
    columns: ['user_id']
)]
#[HasLifecycleCallbacks]
class PostTag extends AbstractEntity
{
    public const TABLE_NAME = 'post_tags';

    #[Id]
    #[GeneratedValue('AUTO')]
    #[Column(
        name: 'id',
        type: Types::BIGINT,
        length: 20,
        updatable: false,
        options: [
            'unsigned' => true,
            'comment' => 'Primary key post tag id'
        ]
    )]
    protected int $id;

    #[Column(
        name: 'site_id',
        type: Types::BIGINT,
        length: 20,
        nullable: true,
        options: [
            'default' => null,
            'unsigned' => true,
            'comment' => 'Site id'
        ]
    )]
    protected ?int $site_id = null;

    #[Column(
        name: 'name',
        type: Types::STRING,
        length: 255,
        nullable: false,
        options: [
            'comment' => 'Tag name'
        ]
    )]
    protected string $name;

    #[Column(
        name: 'description',
        type: Types::TEXT,
        length: AbstractMySQLPlatform::LENGTH_LIMIT_TEXT,
        nullable: true,
        options:  [
            'default' => null,
            'comment' => 'Tag description'
        ]
    )]
    protected ?string $description = null;

    #[Column(
        name: 'slug',
        type: Types::STRING,
        length: 255,
        nullable: false,
        options: [
            'comment' => 'Unique slug for tag'
        ]
    )]
    protected string $slug;

    #[Column(
        name: 'user_id',
        type: Types::BIGINT,
        length: 20,
        nullable: true,
        options:  [
            'default' => null,
            'unsigned' => true,
            'comment' => 'Admin id'
        ]
    )]
    protected ?int $user_id = null;

    #[Column(
        name: 'created_at',
        type: Types::DATETIME_MUTABLE,
        updatable: false,
        options: [
            'default' => 'CURRENT_TIMESTAMP',
            'comment' => 'Category created time'
        ]
    )]
    protected DateTimeInterface $created_at;

    #[Column(
        name: 'updated_at',
        type: Types::DATETIME_IMMUTABLE,
        unique: false,
        updatable: false,
        options: [
            'attribute' => 'ON UPDATE CURRENT_TIMESTAMP', // this column attribute
            'default' => '0000-00-00 00:00:00',
            'comment' => 'Category update time'
        ],
        // columnDefinition: "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP"
    )]
    protected DateTimeInterface $updated_at;

    #[
        JoinColumn(
            name: 'site_id',
            referencedColumnName: 'id',
            nullable: true,
            onDelete: 'RESTRICT',
            options: [
                'relation_name' => 'relation_post_tags_site_id_sites_id',
                'onUpdate' => 'CASCADE',
                'onDelete' => 'RESTRICT'
            ]
        ),
        ManyToOne(
            targetEntity: Site::class,
            cascade: [
                "persist"
            ],
            fetch: 'EAGER'
        )
    ]
    protected ?Site $site;

    #[
        JoinColumn(
            name: 'user_id',
            referencedColumnName: 'id',
            nullable: true,
            onDelete: 'SET NULL',
            options: [
                'relation_name' => 'relation_post_tags_user_id_admins_id',
                'onUpdate' => 'CASCADE',
                'onDelete' => 'SET NULL'
            ],
        ),
        ManyToOne(
            targetEntity: Admin::class,
            cascade: [
                'persist'
            ],
            fetch: 'LAZY'
        )
    ]
    protected ?Admin $user = null;

    #[
        JoinTable(name: PostTagList::TABLE_NAME),
        JoinColumn(
            name: 'id',
            referencedColumnName: 'tag_id',
            nullable: false,
            onDelete: 'CASCADE',
            options: [
                'relation_name' => 'relation_post_tag_list_tag_id_post_tags_id',
                'onUpdate' => 'CASCADE',
                'onDelete' => 'CASCADE',
            ]
        ),
        OneToMany(
            targetEntity: PostTagList::class,
            mappedBy: 'tag',
            cascade: [
                "persist",
                "remove",
                // "merge",
                "detach"
            ],
            fetch: 'EXTRA_LAZY'
        )
    ]
    protected ?Collection $tag_list = null;

    public function __construct()
    {
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable('0000-00-00 00:00:00');
    }

    #[
        PrePersist,
        PreUpdate
    ]
    public function preCheckSlug(PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        /** @noinspection DuplicatedCode */
        $oldSlug = null;
        $slug = $this->getSlug();
        $isUpdate = $event instanceof PreUpdateEventArgs;
        if ($isUpdate) {
            if (!$event->hasChangedField('slug')) {
                return;
            }
            $oldSlug = $event->getOldValue('slug');
            $slug = $event->getNewValue('slug')?:$slug;
        }

        if ($oldSlug === $slug) {
            return;
        }

        if (trim($slug) === '') {
            $slug = UUID::v4();
        }
        do {
            $this->slug = $slug;
            $query = $event
                ->getObjectManager()
                ->getRepository($this::class)
                ->matching(
                    Expression::criteria()->where(
                        Expression::andX(
                            Expression::eq('slug', $slug),
                            Expression::eq('site_id', $this->getSite()),
                        )
                    )->setMaxResults(1)
                )
                ->count();
        } while ($query > 0 && ($slug = UUID::v4()));
        if ($isUpdate) {
            $event->setNewValue('slug', $slug);
        }
    }

    public function getId() : int
    {
        return $this->id;
    }
    
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updated_at;
    }

    public function getParentId(): ?int
    {
        return $this->parent_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getUser(): ?Admin
    {
        return $this->user;
    }

    public function setUser(?Admin $user): void
    {
        $this->user = $user;
        $this->setUserId($user?->getId());
    }

    public function getSiteId(): ?int
    {
        return $this->site_id;
    }

    public function setSiteId(?int $site_id): void
    {
        $this->site_id = $site_id;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): void
    {
        $this->site = $site;
        $this->setSiteId($site?->getId());
    }

    public function getTagList(): ?Collection
    {
        return $this->tag_list;
    }
}
