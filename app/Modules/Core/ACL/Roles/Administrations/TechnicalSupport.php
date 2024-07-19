<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\ACL\AbstractAdminRole;

class TechnicalSupport extends AbstractAdminRole
{
    protected string $name = 'Technical Support';

    protected string $identity = 'technical_support';

    protected ?string $description = 'Technical support role capabilities, can manage tickets and access dashboard';
}
