<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Supports;

class CanCreateTickets extends CanManageTickets
{
    public const ID = 'can_create_tickets';

    protected string $name = 'Can Create Tickets';

    protected string $identity = self::ID;

    protected ?string $description = 'Can create tickets capabilities, the role can create new tickets';
}
