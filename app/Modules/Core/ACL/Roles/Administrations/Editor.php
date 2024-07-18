<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations;

use ArrayAccess\TrayDigita\Auth\Roles\AbstractRole;

class Editor extends AbstractRole
{
    protected string $name = 'Editor';

    protected string $identity = 'editor';

    protected ?string $description = 'Editor role capabilities, can create or edit posts';
}
