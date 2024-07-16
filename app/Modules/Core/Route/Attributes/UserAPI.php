<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class UserAPI extends AbstractAPIAttributes
{
    public const API_SUB_PREFIX = 'user';
}
