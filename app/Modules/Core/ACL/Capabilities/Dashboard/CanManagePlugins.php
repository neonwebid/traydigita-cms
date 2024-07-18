<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\Dashboard;

use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Traits\CapabilityRegistrationTrait;
use ArrayAccess\TrayDigita\Auth\Roles\AbstractCapability;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;
use ArrayAccess\TrayDigita\Container\Interfaces\ContainerAllocatorInterface;

class CanManagePlugins extends AbstractCapability implements ContainerAllocatorInterface
{
    public const ID = 'can_manage_plugins';

    use CapabilityRegistrationTrait;

    protected string $name = 'Can Manage Plugins';

    protected string $identity = self::ID;

    protected ?string $description = 'Can manage plugins capabilities';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class
        ];
    }
}
