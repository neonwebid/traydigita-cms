<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Traits;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\Exceptions\Runtime\RuntimeException;
use function sprintf;

trait CoreModuleAssertionTrait
{
    private function assertObjectCoreModule(): void
    {
        /** @noinspection PhpInstanceofIsAlwaysTrueInspection */
        if (!$this instanceof Core) {
            throw new RuntimeException(
                sprintf(
                    'Object trait should instance of : %s',
                    Core::class
                )
            );
        }
    }
}
