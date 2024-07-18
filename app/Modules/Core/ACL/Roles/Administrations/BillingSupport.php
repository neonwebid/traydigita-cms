<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\ACL\Roles\Administrations;

use ArrayAccess\TrayDigita\Auth\Roles\AbstractRole;

class BillingSupport extends AbstractRole
{
    protected string $name = 'Billing Support';

    protected string $identity = 'billing_support';

    protected ?string $description = 'Billing support role capabilities, can manage billing and access dashboard';
}
