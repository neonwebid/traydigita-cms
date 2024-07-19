<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Posts;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Author;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Contributor;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Editor;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;

class CanViewPost extends AbstractAdminCapability
{
    public const ID = 'can_view_post';

    protected string $name = 'Can View Post';

    protected string $identity = self::ID;

    protected ?string $description = 'Can view post capabilities, can view post by himself';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class,
            Editor::class,
            Author::class,
            Contributor::class
        ];
    }
}
