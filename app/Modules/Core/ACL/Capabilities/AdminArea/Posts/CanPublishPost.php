<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\AdminArea\Posts;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Author;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Editor;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;

class CanPublishPost extends AbstractAdminCapability
{
    public const ID = 'can_publish_post';

    protected string $name = 'Can Publish Post';

    protected string $identity = self::ID;

    protected ?string $description = 'Can publish post capabilities, can publish post by himself';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class,
            Editor::class,
            Author::class
        ];
    }
}
