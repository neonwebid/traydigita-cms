<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL;

use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Commons\Unknown;
use ArrayAccess\TrayDigita\App\Modules\Core\Static\RoleStatic;
use ArrayAccess\TrayDigita\Auth\Roles\AbstractCapability;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\RoleInterface;
use ArrayAccess\TrayDigita\Container\Interfaces\ContainerAllocatorInterface;
use ArrayAccess\TrayDigita\Traits\Container\ContainerAllocatorTrait;
use ArrayAccess\TrayDigita\Util\Filter\Consolidation;

abstract class AbstractCoreACLCapability extends AbstractCapability implements ContainerAllocatorInterface
{
    use ContainerAllocatorTrait;

    /**
     * @param RoleInterface|string $role
     * @return ?RoleInterface
     */
    final public function add(RoleInterface|string $role): ?RoleInterface
    {
        $role = is_string($role) ? RoleStatic::gerRoleForFromPermission($role) : $role;
        // do not add empty roles
        if (!$role instanceof RoleInterface || $role->getRole() === '') {
            return null;
        }

        // do not add admin capabilities to non admin roles
        if ($this instanceof AbstractAdminCapability && !$role instanceof AbstractAdminRole) {
            return null;
        }

        // do not add unknown roles for admin and user capabilities
        if ($role instanceof Unknown && (
                $this instanceof AbstractAdminCapability || $this instanceof AbstractUserCapability
            )
        ) {
            return null;
        }

        return parent::add($role);
    }

    /**
     * @return bool Is the capability for admin
     */
    public function isAdminCapability(): bool
    {
        return $this instanceof AbstractAdminCapability;
    }

    public function isUserCapability(): bool
    {
        return $this instanceof AbstractUserCapability;
    }

    /**
     * Magic method to get the debug info
     *
     * @return ?array
     */
    public function __debugInfo(): ?array
    {
        return Consolidation::debugInfo($this, excludeKeys: ['roles']);
    }
}
