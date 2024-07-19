<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL;

use ArrayAccess\TrayDigita\App\Modules\Core\ACL\Traits\CapabilityRegistrationTrait;

abstract class AbstractACLCapability extends AbstractCoreACLCapability
{
    use CapabilityRegistrationTrait;
}
