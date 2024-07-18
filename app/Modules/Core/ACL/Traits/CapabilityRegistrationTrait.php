<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Traits;

use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\PermissionInterface;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\RoleInterface;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminCapability;
use ArrayAccess\TrayDigita\Auth\Roles\SuperAdminRole;
use ArrayAccess\TrayDigita\Traits\Container\ContainerAllocatorTrait;
use ArrayAccess\TrayDigita\Util\Filter\Consolidation;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;
use Throwable;

trait CapabilityRegistrationTrait
{
    use ContainerAllocatorTrait;

    /**
     * @var bool $constructedRegister Flag to check if the roles have been registered
     */
    private bool $constructedRegister = false;

    /**
     * @return array<class-string<RoleInterface>
     */
    abstract protected function getRoleClassList(): array;

    /**
     * Register the roles
     *
     * @return void
     */
    protected function onConstruct() : void
    {
        if ($this->constructedRegister) {
            return;
        }
        $this->constructedRegister = true;
        $permission = ContainerHelper::use(PermissionInterface::class, $this->getContainer());
        foreach ($this->getRoleClassList() as $role) {
            if ($role instanceof RoleInterface) {
                $this->add($role);
                continue;
            }
            if (!is_string($role)) {
                continue;
            }
            if (is_subclass_of($role, RoleInterface::class)) {
                // if it has super admin role, get the role from the permission
                if (is_a($role, SuperAdminRole::class, true)) {
                    $role = $permission->get(SuperAdminCapability::NAME)?->getRoles()[SuperAdminRole::NAME]??$role;
                }
                try {
                    $role = ContainerHelper::resolveCallable($role);
                } catch (Throwable) {
                    continue;
                }
                if (!$role instanceof RoleInterface) {
                    continue;
                }
                $this->add($role);
                continue;
            }
            if (!$permission?->has($role)) {
                continue;
            }
            $role = $permission->get($role);
            if ($role instanceof RoleInterface) {
                $this->add($role);
            }
        }
    }

    public function __debugInfo(): ?array
    {
        return Consolidation::debugInfo($this, excludeKeys: ['roles']);
    }
}
