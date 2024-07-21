<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\TwigExtensions;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\AbstractCoreTwigExtension;
use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use Twig\TwigFunction;

class CoreExtension extends AbstractCoreTwigExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'core',
                fn () : Core => $this->core
            ),
            new TwigFunction(
                'current_mode',
                fn () : string => $this->core->getCurrentMode()
            ),
            new TwigFunction(
                'is_admin_mode',
                fn () : bool => $this->core->isAdminMode()
            ),
            new TwigFunction(
                'is_user_mode',
                fn () : bool => $this->core->isUserMode()
            ),
            new TwigFunction(
                'is_dashboard_page',
                fn () : bool => $this->core->isDashboardPath()
            ),
            new TwigFunction(
                'is_user_page',
                fn () : bool => $this->core->isUserPath()
            ),
        ];
    }
}
