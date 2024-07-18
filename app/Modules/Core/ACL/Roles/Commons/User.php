<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Commons;

use ArrayAccess\TrayDigita\Auth\Roles\AbstractRole;

class User extends AbstractRole
{
    protected string $name = 'User';

    protected string $identity = 'user';

    protected ?string $description = 'User role capabilities, the common user';
}
