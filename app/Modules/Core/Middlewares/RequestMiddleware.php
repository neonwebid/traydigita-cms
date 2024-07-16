<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Middlewares;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\AbstractCoreMiddleware;
use Psr\Http\Message\ServerRequestInterface;

class RequestMiddleware extends AbstractCoreMiddleware
{
    /**
     * @inheritdoc
     */
    protected function doProcess(ServerRequestInterface $request): ServerRequestInterface
    {
        $this->core->setRequest($request);
        return $request;
    }
}
