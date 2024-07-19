<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminRole;

class Supervisor extends AbstractAdminRole
{
    protected string $name = 'Supervisor';

    protected string $identity = 'supervisor';

    protected ?string $description = 'Supervisor role capabilities, can manage tickets and access dashboard';
}
