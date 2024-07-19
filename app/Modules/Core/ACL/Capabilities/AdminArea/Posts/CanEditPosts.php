<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Posts;

class CanEditPosts extends CanManagePosts
{
    public const ID = 'can_edit_posts';

    protected string $name = 'Can Edit Posts';

    protected string $identity = self::ID;

    protected ?string $description =
        'Can edit post capabilities, can edit an existing posts by himself or the other user';
}
