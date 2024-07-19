<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminRole;

class Admin extends AbstractAdminRole
{
    protected string $name = 'Admin';

    protected string $identity = 'admin';

    protected ?string $description = 'Admin role capabilities, can do anything except super admin capabilities';
}
