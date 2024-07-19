<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Posts;

class CanPublishPosts extends CanManagePosts
{
    public const ID = 'can_publish_posts';

    protected string $name = 'Can Publish Posts';

    protected string $identity = self::ID;

    protected ?string $description = 'Can publish posts capabilities, can publish post by himself or the other user';
}
