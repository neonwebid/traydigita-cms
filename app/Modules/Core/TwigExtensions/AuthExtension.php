<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\TwigExtensions;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\AbstractCoreTwigExtension;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\User;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\CapabilityInterface;
use ArrayAccess\TrayDigita\Database\Entities\Abstracts\AbstractUser;
use Twig\TwigFunction;

class AuthExtension extends AbstractCoreTwigExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'is_login',
                fn () : bool => $this->core->isLoggedIn() === true
            ),
            new TwigFunction(
                'user_account',
                fn () : ?User =>$this->core->getUserAccount()
            ),
            new TwigFunction(
                'admin_account',
                fn () : ?Admin => $this->core->getAdminAccount()
            ),
            new TwigFunction(
                'current_account',
                fn () : ?AbstractUser => $this->core->getAccount()
            ),
            new TwigFunction(
                'user_by_id',
                fn ($id = null) : ?User => (is_numeric($id) && !str_contains((string)$id, '.'))
                    ? $this->core->getUserById($id)
                    : null
            ),
            new TwigFunction(
                'user_by_email',
                fn ($email = null) : ?User => is_string($email) ? $this->core->getUserByUsername($email) : null
            ),
            new TwigFunction(
                'user_by_username',
                fn ($username = null) : ?User => is_string($username) ? $this->core->getUserByUsername($username) : null
            ),
            new TwigFunction(
                'admin_by_id',
                fn ($id = null) : ?Admin => (is_numeric($id) && !str_contains((string)$id, '.'))
                    ? $this->core->getAdminById((int) $id)
                    : null
            ),
            new TwigFunction(
                'admin_by_email',
                fn ($email = null) : ?Admin => is_string($email) ? $this->core->getAdminByUsername($email) : null
            ),
            new TwigFunction(
                'admin_by_username',
                fn ($username = null) : ?Admin => is_string($username)
                    ? $this->core->getAdminByUsername($username)
                    : null
            ),
            new TwigFunction(
                'is_allowed',
                fn ($capability = null) : bool => (is_string($capability) || $capability instanceof CapabilityInterface)
                    && $this->core->permitted($capability),
            ),
        ];
    }
}
