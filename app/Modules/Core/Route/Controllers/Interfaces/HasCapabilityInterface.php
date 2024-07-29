<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers\Interfaces;

use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\CapabilityInterface;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\RoleInterface;

interface HasCapabilityInterface extends BaseControllerInterface
{
    /**
     * Get the capabilities required to access the controller
     *
     * @return array<CapabilityInterface>|CapabilityInterface
     */
    public function getControllerCapabilities(): array|CapabilityInterface;

    /**
     * Get current role
     *
     * @return ?RoleInterface
     */
    public function getControllerRole(): ?RoleInterface;

    /**
     * Check if the current role has the required capabilities
     *
     * @return bool
     */
    public function controllerPermitted(): bool;
}
