<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Dashboard;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;

class CanManageOptions extends AbstractAdminCapability
{
    public const ID = 'can_manage_options';

    protected string $name = 'Can Manage Options';

    protected string $identity = self::ID;

    protected ?string $description = 'Can manage options capabilities';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class,
        ];
    }
}
