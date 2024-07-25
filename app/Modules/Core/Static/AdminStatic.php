<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Static;

use ArrayAccess\TrayDigita\Database\Entities\Interfaces\UserEntityInterface;

final class AdminStatic
{
    public static function findByUsername(string $username): ?UserEntityInterface
    {
        return CoreModuleStatic::core()?->finder->admin->findByUsername($username);
    }

    public static function findByEmail(string $email): ?UserEntityInterface
    {
        return CoreModuleStatic::core()?->finder->admin->findByEmail($email);
    }

    public static function findById(int $id): ?UserEntityInterface
    {
        return CoreModuleStatic::core()?->finder->admin->findById($id);
    }

    public static function findByUsernameOrEmail(string $usernameOrEmail): ?UserEntityInterface
    {
        return str_contains($usernameOrEmail, '@')
            ? self::findByEmail($usernameOrEmail)
            : self::findByUsername($usernameOrEmail);
    }
}
