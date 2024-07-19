<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Commons;

use ArrayAccess\TrayDigita\Auth\Roles\AbstractRole;

class Unknown extends AbstractRole
{
    protected string $name = 'Unknown';

    protected string $identity = 'unknown';

    protected ?string $description = 'Unknown role, this non user role';
}
