<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Static;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\Database\Entities\Interfaces\UserEntityInterface;

final class Account
{
    /**
     * @return ?class-string<User|Admin>
     */
    public static function getUserMode() : ?string
    {
        if (!CoreModule::getCore()) {
            return null;
        }
        return CoreModule::getCore()->getCurrentMode() === Core::ADMIN_MODE
            ? Admin::class
            : User::class;
    }

    public static function findByUsername(string $username): ?UserEntityInterface
    {
        $mode = static::getUserMode();
        return $mode ? $mode::findByUsername($username) : null;
    }

    public static function findByEmail(string $email): ?UserEntityInterface
    {
        $mode = static::getUserMode();
        return $mode ? $mode::findByEmail($email) : null;
    }

    public static function findById(int $id): ?UserEntityInterface
    {
        $mode = static::getUserMode();
        return $mode ? $mode::findById($id) : null;
    }

    public static function findByUsernameOrEmail(string $usernameOrEmail): ?UserEntityInterface
    {
        $mode = static::getUserMode();
        return $mode ? $mode::findByUsernameOrEmail($usernameOrEmail) : null;
    }
}
