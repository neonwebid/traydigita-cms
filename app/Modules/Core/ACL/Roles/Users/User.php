<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Users;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractUserRole;

class User extends AbstractUserRole
{
    protected string $name = 'User';

    protected string $identity = 'user';

    protected ?string $description = 'User role capabilities, the common user';
}
