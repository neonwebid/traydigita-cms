<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use Psr\Http\Message\ServerRequestInterface;
use function is_string;
use function reset;
use function str_ends_with;
use function str_starts_with;

abstract class AbstractUserController extends AbstractAdministrationController
{
    protected ?string $authenticationMethod = Core::USER_MODE;

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
     * @return \Psr\Http\Message\ResponseInterface|void|null
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public function doAfterBeforeDispatch(
        ServerRequestInterface $request,
        string $method,
        ...$arguments
    ) {
        $reset = reset($arguments);
        if ($request->getMethod() !== 'GET'
            || !($path = (reset($reset)?:[])[0]??null)
            || !is_string($path)
        ) {
            return null;
        }
        if (($end = str_ends_with($path, '//')) || str_starts_with($path, '//')) {
            return $this->redirect(
                $this->getView()->getBaseURI(
                    '/'.
                    trim($path, '/')
                    . ($end ? '/': '')
                )
            );
        }
    }
}
