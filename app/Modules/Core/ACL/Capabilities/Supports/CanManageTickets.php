<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\Supports;

use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Supervisor;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Traits\CapabilityRegistrationTrait;
use ArrayAccess\TrayDigita\Auth\Roles\AbstractCapability;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;
use ArrayAccess\TrayDigita\Container\Interfaces\ContainerAllocatorInterface;

class CanManageTickets extends AbstractCapability implements ContainerAllocatorInterface
{
    public const ID = 'can_manage_tickets';

    use CapabilityRegistrationTrait;

    protected string $name = 'Can Manage Tickets';

    protected string $identity = self::ID;

    protected ?string $description = 'Can manage tickets capabilities';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class,
            Supervisor::class,
        ];
    }
}
