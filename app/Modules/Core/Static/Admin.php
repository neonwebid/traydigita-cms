<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Static;

use ArrayAccess\TrayDigita\Database\Entities\Interfaces\UserEntityInterface;

final class Admin
{
    public static function findByUsername(string $username): ?UserEntityInterface
    {
        return CoreModule::getCore()?->getAdminEntityFactory()->findByUsername($username);
    }

    public static function findByEmail(string $email): ?UserEntityInterface
    {
        return CoreModule::getCore()?->getAdminEntityFactory()->findByEmail($email);
    }

    public static function findById(int $id): ?UserEntityInterface
    {
        return CoreModule::getCore()?->getAdminEntityFactory()->findById($id);
    }

    public static function findByUsernameOrEmail(string $usernameOrEmail): ?UserEntityInterface
    {
        return str_contains($usernameOrEmail, '@')
            ? self::findByEmail($usernameOrEmail)
            : self::findByUsername($usernameOrEmail);
    }
}
