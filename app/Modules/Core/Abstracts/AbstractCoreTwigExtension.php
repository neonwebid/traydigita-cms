<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Abstracts;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\View\Engines\TwigEngine;
use Twig\Extension\AbstractExtension;

abstract class AbstractCoreTwigExtension extends AbstractExtension
{
    public function __construct(public readonly TwigEngine $engine, public readonly Core $core)
    {
    }
}
