<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Static;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\Database\Entities\Interfaces\UserEntityInterface;

final class AccountStatic
{
    /**
     * @return ?class-string<UserStatic|AdminStatic>
     */
    public static function getUserMode() : ?string
    {
        if (!CoreModuleStatic::core()) {
            return null;
        }
        return CoreModuleStatic::core()->getCurrentMode() === Core::ADMIN_MODE
            ? AdminStatic::class
            : UserStatic::class;
    }

    public static function findByUsername(string $username): ?UserEntityInterface
    {
        $mode = self::getUserMode();
        return $mode ? $mode::findByUsername($username) : null;
    }

    public static function findByEmail(string $email): ?UserEntityInterface
    {
        $mode = self::getUserMode();
        return $mode ? $mode::findByEmail($email) : null;
    }

    public static function findById(int $id): ?UserEntityInterface
    {
        $mode = self::getUserMode();
        return $mode ? $mode::findById($id) : null;
    }

    public static function findByUsernameOrEmail(string $usernameOrEmail): ?UserEntityInterface
    {
        $mode = self::getUserMode();
        return $mode ? $mode::findByUsernameOrEmail($usernameOrEmail) : null;
    }
}
