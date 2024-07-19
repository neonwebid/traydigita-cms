<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Supports;

class CanDeleteBillingTickets extends CanManageBillingTickets
{
    public const ID = 'can_delete_billing_tickets';

    protected string $name = 'Can Delete Billing Tickets';

    protected string $identity = self::ID;

    protected ?string $description = 'Can delete billing tickets capabilities, the role can delete billing tickets';
}
