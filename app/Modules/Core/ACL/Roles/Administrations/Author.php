<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminRole;

class Author extends AbstractAdminRole
{
    protected string $name = 'Author';

    protected string $identity = 'author';

    protected ?string $description = 'Author role capabilities, the author can only create and edit posts';
}
