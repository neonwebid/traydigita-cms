<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\Posts;

use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Traits\CapabilityRegistrationTrait;

class CanDeletePosts extends CanManagePosts
{
    public const ID = 'can_delete_posts';

    use CapabilityRegistrationTrait;

    protected string $name = 'Can Edit Posts';

    protected string $identity = self::ID;

    protected ?string $description = 'Can delete posts capabilities, can delete any posts on dashboard';
}
