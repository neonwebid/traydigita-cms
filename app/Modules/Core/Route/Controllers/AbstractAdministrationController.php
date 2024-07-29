<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers\Interfaces\HasCapabilityInterface;
use ArrayAccess\TrayDigita\Util\Filter\DataNormalizer;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractAdministrationController extends AbstractController implements
    HasCapabilityInterface
{
    protected bool $doRedirect = true;

    /**
     * Doing check
     *
     * @param ServerRequestInterface $request
     * @param string $method
     * @param ...$arguments
     * @noinspection PhpDocSignatureIsNotCompleteInspection
     */
    final public function doBeforeDispatch(
        ServerRequestInterface $request,
        string $method,
        ...$arguments
    ) {
        $redirect = $this->doRedirect ? match ($this->getAuthenticationMethod()) {
            Core::ADMIN_MODE => $this->getControllerCoreModule()->getAdminAccount() ? null : $this->dashboardAuthPath,
            Core::USER_MODE => $this->getControllerCoreModule()->getUserAccount() ? null : $this->userAuthPath,
            default => null,
        } : null;
        return $redirect
            ? $this->redirect(
                $this
                    ->getView()
                    ->getBaseURI($redirect)
                    ->withQuery(
                        'redirect='
                        . DataNormalizer::normalizeUnixDirectorySeparator($request->getUri()->getPath())
                    )
            ) : $this->doAfterBeforeDispatch($request, $method, $arguments);
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $method
     * @param ...$arguments
     */
    abstract public function doAfterBeforeDispatch(
        ServerRequestInterface $request,
        string $method,
        ...$arguments
    );
}
