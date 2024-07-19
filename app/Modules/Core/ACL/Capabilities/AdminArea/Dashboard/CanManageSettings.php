<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Dashboard;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminCapability;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;

class CanManageSettings extends AbstractAdminCapability
{
    public const ID = 'can_manage_settings';

    protected string $name = 'Can Manage Settings';

    protected string $identity = self::ID;

    protected ?string $description = 'Can manage settings capabilities';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
        ];
    }
}
