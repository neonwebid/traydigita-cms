<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\Users;

use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Traits\CapabilityRegistrationTrait;
use ArrayAccess\TrayDigita\Auth\Roles\AbstractCapability;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;
use ArrayAccess\TrayDigita\Container\Interfaces\ContainerAllocatorInterface;

class CanCreateUsers extends AbstractCapability implements ContainerAllocatorInterface
{
    public const ID = 'can_create_users';

    use CapabilityRegistrationTrait;

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
