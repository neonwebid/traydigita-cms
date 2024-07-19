<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Supports;

class CanCreateTechnicalTickets extends CanManageTechnicalTickets
{
    public const ID = 'can_create_technical_tickets';

    protected string $name = 'Can Create Technical Tickets';

    protected string $identity = self::ID;

    protected ?string $description =
        'Can create technical tickets capabilities, the role can create new technical tickets';
}
