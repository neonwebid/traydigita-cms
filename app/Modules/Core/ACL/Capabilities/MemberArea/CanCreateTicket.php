<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\MemberArea;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractUserCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Author;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Contributor;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Editor;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Users\User;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;

class CanCreateTicket extends AbstractUserCapability
{
    public const ID = 'can_create_ticket';

    protected string $name = 'Can Create Ticket';

    protected string $identity = self::ID;

    protected ?string $description = 'Can create ticket capabilities';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class,
            Editor::class,
            Author::class,
            Contributor::class,
            User::class,
        ];
    }
}
