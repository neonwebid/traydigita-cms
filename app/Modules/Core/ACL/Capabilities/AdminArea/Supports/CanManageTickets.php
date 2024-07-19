<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Supports;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Supervisor;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;

class CanManageTickets extends AbstractAdminCapability
{
    public const ID = 'can_manage_tickets';

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
