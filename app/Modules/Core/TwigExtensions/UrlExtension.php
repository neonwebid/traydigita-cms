<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\TwigExtensions;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\AbstractCoreTwigExtension;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\DashboardAPI;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\RouteAPI;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\UserAPI;
use Twig\TwigFunction;

class UrlExtension extends AbstractCoreTwigExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'api_url',
                fn ($path = '') => RouteAPI::baseURI($this->engine->getView(), (string) $path)
            ),
            new TwigFunction(
                'user_api_url',
                fn ($path = '') => UserAPI::baseURI($this->engine->getView(), (string) $path)
            ),
            new TwigFunction(
                'dashboard_api_url',
                fn ($path = '') => DashboardAPI::baseURI($this->engine->getView(), (string) $path)
            )
        ];
    }
}
