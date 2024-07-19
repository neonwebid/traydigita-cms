<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Users;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;

class CanEditUsers extends AbstractAdminCapability
{
    public const ID = 'can_edit_users';

    protected string $name = 'Can Edit Users';

    protected string $identity = self::ID;

    protected ?string $description = 'Can edit user capabilities, can edit an existing user';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class,
        ];
    }
}
