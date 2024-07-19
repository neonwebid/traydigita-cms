<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Capabilities\Miscellaneous;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractCoreACLCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Commons\Unknown;

class UnknownCapability extends AbstractCoreACLCapability
{
    public const ID = 'unknown';

    protected string $identity = self::ID;

    protected string $name = 'Unknown';

    protected ?string $description = 'Unknown capability';

    protected function onConstruct(): void
    {
        $this->add(new Unknown());
    }
}
