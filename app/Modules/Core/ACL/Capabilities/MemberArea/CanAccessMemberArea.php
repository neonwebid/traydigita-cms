<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\MemberArea;

use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Author;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations\Editor;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Commons\Contributor;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Commons\User;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Traits\CapabilityRegistrationTrait;
use ArrayAccess\TrayDigita\Auth\Roles\AbstractCapability;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;
use ArrayAccess\TrayDigita\Container\Interfaces\ContainerAllocatorInterface;

class CanAccessMemberArea extends AbstractCapability implements ContainerAllocatorInterface
{
    public const ID = 'can_access_member_area';

    use CapabilityRegistrationTrait;

    protected string $name = 'Can Access Member Area';

    protected string $identity = self::ID;

    protected ?string $description = 'Can access member area capabilities';

    protected function getRoleClassList(): array
    {
        return [
            SuperAdminRole::class,
            Admin::class,
            Editor::class,
            Author::class,
            Contributor::class,
            User::class,
        ];
    }
}
