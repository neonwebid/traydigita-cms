<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Factory;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\User;
use ArrayAccess\TrayDigita\Auth\Cookie\Interfaces\UserEntityFactoryInterface;

class UserEntityFactory implements UserEntityFactoryInterface
{
    public function __construct(protected Core $core)
    {
    }

    public function findById(int $id): ?User
    {
        return $this->core->finder->user->findById($id);
    }

    public function findByUsername(string $username) : ?User
    {
        return $this->core->finder->user->findByUsername($username);
    }

    public function findByEmail(string $email) : ?User
    {
        return $this->core->finder->user->findByEmail($email);
    }
}
