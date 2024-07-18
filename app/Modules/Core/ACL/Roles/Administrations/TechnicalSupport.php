<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations;

use ArrayAccess\TrayDigita\Auth\Roles\AbstractRole;

class TechnicalSupport extends AbstractRole
{
    protected string $name = 'Technical Support';

    protected string $identity = 'technical_support';

    protected ?string $description = 'Technical support role capabilities, can manage tickets and access dashboard';
}
