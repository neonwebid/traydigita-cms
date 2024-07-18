<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Commons;

use ArrayAccess\TrayDigita\Auth\Roles\AbstractRole;

class Contributor extends AbstractRole
{
    protected string $name = 'Contributor';

    protected string $identity = 'contributor';

    protected ?string $description = 'Contributor role capabilities, can only create posts';
}
