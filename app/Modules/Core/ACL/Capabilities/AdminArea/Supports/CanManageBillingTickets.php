<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Supports;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\BillingSupport;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Supervisor;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;

class CanManageBillingTickets extends AbstractAdminCapability
{
    public const ID = 'can_manage_billing_tickets';

    protected string $name = 'Can Manage Billing Tickets';

    protected string $identity = self::ID;

    protected ?string $description = 'Can manage billing tickets capabilities';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class,
            Supervisor::class,
            BillingSupport::class
        ];
    }
}
