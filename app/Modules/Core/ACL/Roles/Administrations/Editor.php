<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminRole;

class Editor extends AbstractAdminRole
{
    protected string $name = 'Editor';

    protected string $identity = 'editor';

    protected ?string $description = 'Editor role capabilities, can create or edit posts';
}
