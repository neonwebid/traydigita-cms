<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Supports;

class CanDeleteTechnicalTickets extends CanManageTechnicalTickets
{
    public const ID = 'can_delete_technical_tickets';

    protected string $name = 'Can Delete Technical Tickets';

    protected string $identity = self::ID;

    protected ?string $description = 'Can delete technical tickets capabilities, the role can delete technical tickets';
}
