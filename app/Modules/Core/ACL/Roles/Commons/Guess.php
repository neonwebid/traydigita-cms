<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Commons;

use ArrayAccess\TrayDigita\Auth\Roles\AbstractRole;

class Guess extends AbstractRole
{
    protected string $name = 'Guess';

    protected string $identity = 'guess';

    protected ?string $description = 'Guess role, this non user role';
}
