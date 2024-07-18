<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\Posts;

use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Traits\CapabilityRegistrationTrait;

class CanEditPosts extends CanManagePosts
{
    public const ID = 'can_edit_posts';

    use CapabilityRegistrationTrait;

    protected string $name = 'Can Edit Posts';

    protected string $identity = self::ID;

    protected ?string $description =
        'Can edit post capabilities, can edit an existing posts by himself or the other user';
}
