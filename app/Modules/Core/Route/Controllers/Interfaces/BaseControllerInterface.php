<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers\Interfaces;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\PermissionInterface;
use ArrayAccess\TrayDigita\Routing\Interfaces\ControllerInterface;

interface BaseControllerInterface extends ControllerInterface
{
    /**
     * Get the core module of the controller
     *
     * @return Core
     */
    public function getCoreModule() : Core;

    /**
     * Get the mode of the controller
     *
     * @return ?string
     * @see Core::USER_MODE
     * @see Core::ADMIN_MODE
     */
    public function getControllerUserMode() : ?string;

    /**
     * Get permission of the controller
     *
     * @return PermissionInterface
     */
    public function getControllerPermission() : PermissionInterface;
}
