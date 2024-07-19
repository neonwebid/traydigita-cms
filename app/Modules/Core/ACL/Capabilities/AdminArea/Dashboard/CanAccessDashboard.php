<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Dashboard;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Author;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Contributor;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Editor;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Supervisor;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;

class CanAccessDashboard extends AbstractAdminCapability
{
    public const ID = 'can_access_dashboard';

    protected string $name = 'Can Access Dashboard';

    protected string $identity = self::ID;

    protected ?string $description = 'Can access dashboard capabilities';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class,
            Editor::class,
            Author::class,
            Supervisor::class,
            Contributor::class
        ];
    }
}
