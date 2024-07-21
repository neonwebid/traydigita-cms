<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Static;

use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Commons\Unknown;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\PermissionInterface;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\RoleInterface;
use ArrayAccess\TrayDigita\Database\Entities\Abstracts\AbstractUser;
use ArrayAccess\TrayDigita\Util\Filter\Consolidation;

final class RoleStatic
{
    /**
     * @var Unknown|null $unknownRole Unknown role
     */
    private static ?RoleInterface $unknownRole = null;

    /**
     * @param string|AbstractUser $role
     * @param PermissionInterface|null $permission
     * @return ?RoleInterface
     */
    public static function gerRoleForFromPermission(
        string|AbstractUser $role,
        ?PermissionInterface $permission = null
    ) : ?RoleInterface {
        $role = $role instanceof AbstractUser ? $role->getRole() : $role;
        $role = trim($role);
        if (!$role) {
            return null;
        }
        $permission ??= CoreModuleStatic::core()?->getPermission();
        if (!$permission) {
            return null;
        }
        $isClass = str_contains($role, '\\')
            && Consolidation::isValidClassName($role)
            && is_a($role, RoleInterface::class, true);
        $role = strtolower($role);
        foreach ($permission->getCapabilities() as $capability) {
            foreach ($capability->getRoles() as $r) {
                if ($isClass && is_a($r, $role, true)) {
                    return $r;
                }
                if (strtolower($r->getRole()) === $role) {
                    return $r;
                }
            }
        }
        return null;
    }

    /**
     * @return RoleInterface
     */
    public static function getUnknownRole() : RoleInterface
    {
        if (isset(self::$unknownRole)) {
            return self::$unknownRole;
        }
        $role = self::gerRoleForFromPermission(Unknown::class);
        return self::$unknownRole ??= $role??new Unknown();
    }

    /**
     * @param AbstractUser|null $user
     * @return RoleInterface
     */
    public static function find(?AbstractUser $user = null) : RoleInterface
    {
        if (!$user) {
            return self::getUnknownRole();
        }
        return self::gerRoleForFromPermission($user)??self::getUnknownRole();
    }
}
