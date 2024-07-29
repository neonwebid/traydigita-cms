<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractDashboardController extends AbstractAdministrationController
{
    protected ?string $authenticationMethod = Core::ADMIN_MODE;

    /**
     * @return string
     */
    public function getControllerUserMode(): string
    {
        return $this->authenticationMethod;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $method
     * @param ...$arguments
     */
    public function doAfterBeforeDispatch(
        ServerRequestInterface $request,
        string $method,
        ...$arguments
    ) {
    }
}
