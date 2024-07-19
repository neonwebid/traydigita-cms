<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Dashboard;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;

class CanManageThemes extends AbstractAdminCapability
{
    public const ID = 'can_manage_themes';

    protected string $name = 'Can Manage Themes';

    protected string $identity = self::ID;

    protected ?string $description = 'Can manage themes capabilities';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class
        ];
    }
}
