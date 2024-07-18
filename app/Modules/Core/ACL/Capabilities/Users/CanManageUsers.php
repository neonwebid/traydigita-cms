<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\Users;

use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Traits\CapabilityRegistrationTrait;
use ArrayAccess\TrayDigita\Auth\Roles\AbstractCapability;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;
use ArrayAccess\TrayDigita\Container\Interfaces\ContainerAllocatorInterface;

class CanManageUsers extends AbstractCapability implements ContainerAllocatorInterface
{
    public const ID = 'can_manage_users';

    use CapabilityRegistrationTrait;

    protected string $name = 'Can Manage Users';

    protected string $identity = self::ID;

    protected ?string $description = 'Can manage user capabilities, can create, edit and delete users';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class,
        ];
    }
}
