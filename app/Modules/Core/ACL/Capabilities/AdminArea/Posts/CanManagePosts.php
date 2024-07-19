<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Posts;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Editor;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;

class CanManagePosts extends AbstractAdminCapability
{
    public const ID = 'can_manage_posts';

    protected string $name = 'Can Manage Posts';

    protected string $identity = self::ID;

    protected ?string $description = 'Can manage posts capabilities, can manage all posts';


    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class,
            Editor::class,
        ];
    }
}
