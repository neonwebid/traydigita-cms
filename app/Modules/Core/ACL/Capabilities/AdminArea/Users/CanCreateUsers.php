<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Users;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;

class CanCreateUsers extends AbstractAdminCapability
{
    public const ID = 'can_create_users';

    protected string $name = 'Can Create Users';

    protected string $identity = self::ID;

    protected ?string $description = 'Can create user capabilities, can create a new user';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class,
        ];
    }
}
