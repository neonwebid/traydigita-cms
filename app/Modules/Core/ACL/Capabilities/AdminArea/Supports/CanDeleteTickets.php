<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Supports;

class CanDeleteTickets extends CanManageTickets
{
    public const ID = 'can_delete_tickets';

    protected string $name = 'Can Delete Tickets';

    protected string $identity = self::ID;

    protected ?string $description = 'Can delete tickets capabilities, the role can delete tickets';
}
