<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Factory;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Admin;
use ArrayAccess\TrayDigita\Auth\Cookie\Interfaces\UserEntityFactoryInterface;

class AdminEntityFactory implements UserEntityFactoryInterface
{
    public function __construct(protected Core $core)
    {
    }

    public function findById(int $id): ?Admin
    {
        return $this->core->finder->admin->findById($id);
    }

    public function findByUsername(string $username) : ?Admin
    {
        return $this->core->finder->admin->findByUsername($username);
    }

    public function findByEmail(string $email) : ?Admin
    {
        return $this->core->finder->admin->findByEmail($email);
    }
}
