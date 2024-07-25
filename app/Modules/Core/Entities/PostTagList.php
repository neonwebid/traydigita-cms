<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Entities;

use ArrayAccess\TrayDigita\Database\Entities\Abstracts\AbstractEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table(
    name: self::TABLE_NAME,
    options: [
        'charset' => 'utf8mb4', // remove this or change to utf8 if not use mysql
        'collation' => 'utf8mb4_unicode_ci',  // remove this if not use mysql
        'comment' => 'Post tag list metadata',
        'priority' => 99,
    ]
)]
#[UniqueConstraint(
    name: 'index_tag_id_post_id',
    columns: ['tag_id', 'post_id']
)]
#[Index(
    name: 'index_tag_id',
    columns: ['tag_id']
)]
#[Index(
    name: 'index_post_id',
    columns: ['post_id']
)]
#[HasLifecycleCallbacks]
class PostTagList extends AbstractEntity
{
    public const TABLE_NAME = 'post_tag_list';

    #[Id]
    #[Column(
        name: 'tag_id',
        type: Types::BIGINT,
        length: 20,
        updatable: false,
        options: [
            'unsigned' => true,
            'comment' => 'Post tag id'
        ]
    )]
    protected int $tag_id;

    #[Id]
    #[Column(
        name: 'post_id',
        type: Types::BIGINT,
        length: 20,
        nullable: true,
        options: [
            'default' => null,
            'unsigned' => true,
            'comment' => 'Post id'
        ]
    )]
    protected int $post_id;

    #[
        JoinColumn(
            name: 'tag_id',
            referencedColumnName: 'id',
            nullable: false,
            onDelete: 'CASCADE',
            options: [
                'relation_name' => 'relation_post_tag_list_tag_id_post_tags_id',
                'onUpdate' => 'CASCADE',
                'onDelete' => 'CASCADE',
            ]
        ),
        ManyToOne(
            targetEntity: PostTag::class,
            cascade: [
                "persist",
                "remove",
                // "merge",
                "detach"
            ],
            fetch: 'LAZY'
        )
    ]
    protected PostTag $tag;

    #[
        JoinColumn(
            name: 'post_id',
            referencedColumnName: 'id',
            nullable: false,
            onDelete: 'CASCADE',
            options: [
                'relation_name' => 'relation_post_tag_list_post_id_posts_id',
                'onUpdate' => 'CASCADE',
                'onDelete' => 'CASCADE',
            ]
        ),
        ManyToOne(
            targetEntity: Post::class,
            cascade: [
                "persist",
                "remove",
                // "merge",
                "detach"
            ],
            fetch: 'LAZY'
        )
    ]
    protected Post $post;

    public function getTagId(): int
    {
        return $this->tag_id;
    }

    public function setTagId(int $tag_id): void
    {
        $this->tag_id = $tag_id;
    }

    public function getPostId(): int
    {
        return $this->post_id;
    }

    public function setPostId(int $post_id): void
    {
        $this->post_id = $post_id;
    }
}
