<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Traits;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Commons\Unknown;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\User;
use ArrayAccess\TrayDigita\App\Modules\Core\Factory\CapabilityFactory;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\CapabilityInterface;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\PermissionInterface;
use ArrayAccess\TrayDigita\Auth\Roles\Permission;
use ArrayAccess\TrayDigita\Container\Interfaces\SystemContainerInterface;
use ArrayAccess\TrayDigita\Database\Entities\Interfaces\CapabilityEntityFactoryInterface;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;
use Throwable;

trait CoreModuleUserPermissiveTrait
{
    use CoreModuleAssertionTrait;

    /**
     * @var PermissionInterface Permission
     */
    protected PermissionInterface $permission;

    /**
     * @var bool Permission resolved
     */
    private bool $permissionResolved = false;

    /**
     * Resolve permission
     * @return $this
     */
    private function resolvePermission(): static
    {
        if ($this->permissionResolved) {
            return $this;
        }

        $this->assertObjectCoreModule();
        $this->permissionResolved = true;
        $container = $this->getContainer();
        $manager = $this->getManager();
        if (!$container->has(CapabilityEntityFactoryInterface::class)) {
            if (method_exists($container, 'set')) {
                $container->set(
                    CapabilityEntityFactoryInterface::class,
                    fn () => new CapabilityFactory()
                );
            }
        }
        $hasPermission = $container->has(PermissionInterface::class);
        if ($hasPermission) {
            $permission = ContainerHelper::getNull(
                PermissionInterface::class,
                $container
            );
            if (!$permission instanceof PermissionInterface) {
                if ($container instanceof SystemContainerInterface) {
                    $container->remove(PermissionInterface::class);
                }
                $hasPermission = false;
            }
        }
        if (!$hasPermission) {
            try {
                $permission = ContainerHelper::resolveCallable(Permission::class, $container);
            } catch (Throwable) {
                $permission = new Permission(
                    $container,
                    $manager
                );
            }
        }
        try {
            if (!$container->has(PermissionInterface::class)
                || $container->get(PermissionInterface::class) !== $permission
            ) {
                $container->remove(PermissionInterface::class);
                $container->set(PermissionInterface::class, static fn () => $permission);
            }
        } catch (Throwable) {
        }

        /*
        $connection = $this->getConnection();
        if (!$hasPermission) {
            $permission = new PermissionWrapper(
                $connection,
                $container,
                $manager
            );
            if ($container instanceof SystemContainerInterface) {
                $container->set(PermissionInterface::class, $permission);
            } elseif (method_exists($container, 'set')) {
                $container->set(PermissionInterface::class, fn () => $permission);
            }
        }

        $permission ??= ContainerHelper::service(
            PermissionInterface::class,
            $container
        );
        if (!$permission instanceof PermissionWrapper) {
            $container->remove(PermissionInterface::class);
            $permission = new PermissionWrapper(
                $connection,
                $container,
                $manager,
                $permission
            );
            $container->set(PermissionInterface::class, $permission);
        }

        $this->permission = $permission;
        if ($this->permission instanceof PermissionWrapper
            && !$this->permission->getCapabilityEntityFactory()
        ) {
            $this->permission->setCapabilityEntityFactory(new CapabilityFactory());
        }
        */
        $this->permission = $permission;
        return $this;
    }

    /**
     * @return PermissionInterface
     */
    public function getPermission(): PermissionInterface
    {
        $container = $this->resolvePermission()->getContainer();
        $permission = ContainerHelper::service(PermissionInterface::class, $container);
        /*return $permission instanceof PermissionWrapper
            ? $permission
            : $this->permission;*/
        return $permission instanceof PermissionInterface ? $permission : $this->permission;
    }

    /**
     * Get current account
     *
     * @return User|Admin|null
     */
    abstract public function getAccount() : User|Admin|null;

    /**
     * @param string|CapabilityInterface $capability
     * @param User|Admin|null $account
     * @return bool
     */
    public function permitted(string|CapabilityInterface $capability, User|Admin|null $account = null) : bool
    {
        $account ??= $this->getAccount();
        if (!$account) {
            return false;
        }
        $role = $account->getObjectRole();
        // dont allow unknown roles
        if ($role instanceof Unknown) {
            return false;
        }
        $capability = $this->getPermission()->get($capability);
        if (!$capability) {
            return false;
        }
        if (!$capability->has($this->getRole())) {
            return false;
        }
        // Check if the capability is an admin capability and the account is not an admin
        // this logic for safeguard
        if ($capability instanceof AbstractAdminCapability && !$account instanceof Admin) {
            return false;
        }
        return $this->getPermission()->permitted($role, $capability);
    }
}
