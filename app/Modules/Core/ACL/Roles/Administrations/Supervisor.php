<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations;

use ArrayAccess\TrayDigita\Auth\Roles\AbstractRole;

class Supervisor extends AbstractRole
{
    protected string $name = 'Supervisor';

    protected string $identity = 'supervisor';

    protected ?string $description = 'Supervisor role capabilities, can manage tickets and access dashboard';
}
