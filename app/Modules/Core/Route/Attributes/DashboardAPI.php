<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class DashboardAPI extends AbstractAPIAttributes
{
    public const API_SUB_PREFIX = 'dashboard';
}
