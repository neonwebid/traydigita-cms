<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Factory;

use ArrayAccess\TrayDigita\App\Modules\Core\Entities\User;
use ArrayAccess\TrayDigita\Auth\Cookie\Interfaces\UserEntityFactoryInterface;
use ArrayAccess\TrayDigita\Database\Connection;
use ArrayAccess\TrayDigita\Database\Entities\Interfaces\UserEntityInterface;

class UserEntityFactory implements UserEntityFactoryInterface
{
    public function __construct(protected Connection $connection)
    {
    }

    public function findById(int $id): ?UserEntityInterface
    {
        return $this->connection->find(
            User::class,
            $id
        );
    }

    public function findByUsername(string $username) : ?UserEntityInterface
    {
        return $this->connection->findOneBy(
            User::class,
            ['username' => $username]
        );
    }

    public function findByEmail(string $email) : ?UserEntityInterface
    {
        return $this->connection->findOneBy(
            User::class,
            ['email' => $email]
        );
    }
}
