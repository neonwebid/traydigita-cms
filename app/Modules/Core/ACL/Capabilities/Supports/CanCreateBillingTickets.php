<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\Supports;

class CanCreateBillingTickets extends CanManageBillingTickets
{
    public const ID = 'can_create_billing_tickets';

    protected string $name = 'Can Create Billing Tickets';

    protected string $identity = self::ID;

    protected ?string $description = 'Can create billing tickets capabilities, the role can create new billing tickets';
}
