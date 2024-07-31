<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers\Interfaces\HasCapabilityInterface;
use ArrayAccess\TrayDigita\Collection\Config;
use ArrayAccess\TrayDigita\Http\Code;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use const JSON_PRETTY_PRINT;

class AbstractApiController extends AbstractController implements HasCapabilityInterface
{
    final public function doBeforeDispatch(
        ServerRequestInterface $request,
        string $method,
        ...$arguments
    ): ?ResponseInterface {
        $this->statusCode = 404;
        // set result as json if return is not string
        $this->asJSON = true;

        // pretty
        $env = ContainerHelper::use(Config::class)?->get('environment');
        $env = $env instanceof Config ? $env : null;
        if ($env?->get('prettyJson') === true) {
            $this->getManager()?->attach(
                'jsonResponder.encodeFlags',
                static fn ($flags) => JSON_PRETTY_PRINT|$flags
            );
        }

        if (!$this->controllerPermitted()) {
            return $this->getJsonResponder()->serve(Code::UNAUTHORIZED);
        }

        $method = $this->getAuthenticationMethod();
        if ($method === null) {
            return null;
        }
        $jsonResponder = $this->getJsonResponder();
        $core = $this->getCoreModule();
        $match = match ($method) {
            Core::USER_MODE => $core->getUserAccount()
                ? null
                : $jsonResponder->serve(Code::UNAUTHORIZED),
            Core::ADMIN_MODE => $core->getAdminAccount()
                ? null
                : $jsonResponder->serve(Code::UNAUTHORIZED),
            default => $core->getAdminAccount() || $core->getUserAccount()
                ? null
                : $jsonResponder->serve(Code::UNAUTHORIZED),
        };

        return $match ?? $this->doAfterBeforeDispatch(
            $request,
            $method,
            ...$arguments
        );
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
