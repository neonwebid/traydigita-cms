<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\Dashboard;

use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Traits\CapabilityRegistrationTrait;
use ArrayAccess\TrayDigita\Auth\Roles\AbstractCapability;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;
use ArrayAccess\TrayDigita\Container\Interfaces\ContainerAllocatorInterface;

class CanManageSettings extends AbstractCapability implements ContainerAllocatorInterface
{
    public const ID = 'can_manage_settings';

    use CapabilityRegistrationTrait;

    protected string $name = 'Can Manage Settings';

    protected string $identity = self::ID;

    protected ?string $description = 'Can manage settings capabilities';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
        ];
    }
}
