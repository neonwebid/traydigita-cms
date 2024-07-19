<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminRole;

class Contributor extends AbstractAdminRole
{
    protected string $name = 'Contributor';

    protected string $identity = 'contributor';

    protected ?string $description = 'Contributor role capabilities, can only create posts';
}
