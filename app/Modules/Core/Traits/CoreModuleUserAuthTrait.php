<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Traits;

use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\User;
use ArrayAccess\TrayDigita\App\Modules\Core\Factory\AdminEntityFactory;
use ArrayAccess\TrayDigita\App\Modules\Core\Factory\UserEntityFactory;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\User as UserDashboard;
use ArrayAccess\TrayDigita\App\Modules\Core\Static\RoleStatic;
use ArrayAccess\TrayDigita\Auth\Cookie\UserAuth;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\RoleInterface;
use ArrayAccess\TrayDigita\Collection\Config;
use ArrayAccess\TrayDigita\Container\Interfaces\SystemContainerInterface;
use ArrayAccess\TrayDigita\Database\Entities\Abstracts\AbstractUser;
use ArrayAccess\TrayDigita\Database\Entities\Interfaces\UserEntityInterface;
use ArrayAccess\TrayDigita\Exceptions\Runtime\RuntimeException;
use ArrayAccess\TrayDigita\Http\Factory\ServerRequestFactory;
use ArrayAccess\TrayDigita\Http\ServerRequest;
use ArrayAccess\TrayDigita\Http\SetCookie;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;
use ArrayAccess\TrayDigita\Util\Filter\DataNormalizer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriInterface;
use Throwable;
use function filter_var;
use function is_numeric;
use function is_string;
use function max;
use function preg_replace;
use function time;
use function trim;
use const FILTER_VALIDATE_DOMAIN;

trait CoreModuleUserAuthTrait
{
    use CoreModuleAssertionTrait;

    /**
     * @var ?RoleInterface $defaultRole
     */
    private ?RoleInterface $defaultRole = null;

    /**
     * Admin mode
     *
     * @var "admin"
     */
    public const ADMIN_MODE = 'admin';

    /**
     * User mode
     *
     * @var "user"
     */
    public const USER_MODE = 'user';

    private array $cookieNames = [
        self::USER_MODE => [
            'name' => 'auth_user',
            'lifetime' => 0,
            'wildcard' => false
        ],
        self::ADMIN_MODE => [
            'name' => 'auth_admin',
            'lifetime' => 0,
            'wildcard' => false
        ]
    ];

    /**
     * @var ?User
     */
    private ?User $userAccount = null;

    /**
     * @var ?User
     */
    private ?User $currentUserAccount = null;

    /**
     * @var ?Admin $currentAdminAccount
     */
    private ?Admin $adminAccount = null;

    /**
     * @var ?Admin $currentAdminAccount
     */
    private ?Admin $currentAdminAccount = null;

    private bool $authProcessed = false;

    /**
     * @var string|null $currentMode
     */
    private ?string $currentMode = null;

    private string $defaultMode = self::USER_MODE;

    private bool $cookieResolved = false;

    private function resolveCookieName(): static
    {
        if ($this->cookieResolved) {
            return $this;
        }
        $this->assertObjectCoreModule();

        $this->cookieResolved = true;
        $config = ContainerHelper::use(Config::class, $this->getContainer());
        $cookie = $config->get('cookie');
        if (!$cookie instanceof Config) {
            $cookie = new Config();
            $config->set('cookie', $cookie);
        }
        foreach ($this->cookieNames as $key => $names) {
            $cookieData = $cookie->get($key);
            $cookieData = $cookieData instanceof Config
                ? $cookieData
                : new Config();
            // replace
            $cookie->set($key, $cookieData);
            $cookieName = $cookieData->get('name');
            $cookieName = is_string($cookieName) && trim($cookieName) !== ''
                ? trim($cookieName)
                : $names['name'];
            $cookieName = preg_replace(
                '~[^!#$%&\'*+-.^_`|\~a-z0-9]~i',
                '',
                $cookieName
            );

            $cookieName = $cookieName === '' ? $names['name'] : $cookieName;
            $cookieLifetime = $cookieData->get('lifetime');
            $cookieLifetime = is_numeric($cookieLifetime) ? $cookieLifetime : 0;
            $cookieLifetime = max((int) $cookieLifetime, 0);
            $cookieWildcard = $cookieData->get('wildcard') === true;
            $this->cookieNames[$key]['name'] = $cookieName;
            $this->cookieNames[$key]['wildcard'] = $cookieWildcard;
            $this->cookieNames[$key]['lifetime'] = $cookieLifetime;
        }

        return $this;
    }

    public function isAuthProcessed(): bool
    {
        return $this->authProcessed;
    }

    /**
     * Do process auth
     * @return $this
     */
    private function doProcessAuth(): static
    {
        if (!$this->request || $this->authProcessed) {
            return $this;
        }
        $this->assertObjectCoreModule();
        $this->authProcessed = true;
        $container = $this->getContainer();
        $userAuth = ContainerHelper::service(UserAuth::class, $container);

        $request = $this->getManager()->dispatch('auth.request', $this->request);
        $request = $request instanceof ServerRequestInterface
            ? $request
            : $this->request;
        $userAuth->getHashIdentity()->setUserAgent(
            $request->getHeaderLine('User-Agent')
        );

        $cookieNames = $this->getCookieNames();
        $cookieParams = $request->getCookieParams();
        $adminCookie = $cookieParams[$cookieNames[self::ADMIN_MODE]['name']]??null;
        $adminCookie = !is_string($adminCookie) ? $adminCookie : null;
        $userCookie  = $cookieParams[$cookieNames[self::USER_MODE]['name']]??null;
        $userCookie = is_string($userCookie) ? $userCookie : null;

        $this->currentUserAccount = $userCookie ? $userAuth->getUser(
            $userCookie,
            $this->getUserEntityFactory()
        ) : null;
        $this->currentAdminAccount = $adminCookie ? $userAuth->getUser(
            $adminCookie,
            $this->getAdminEntityFactory()
        ) : null;

        $this->userAccount ??= $this->currentUserAccount;
        $this->adminAccount ??= $this->currentAdminAccount;

        return $this;
    }

    /**
     * Create entity factory container
     *
     * @return $this
     */
    private function createEntityFactoryContainer(): static
    {
        $this->assertObjectCoreModule();
        $container = $this->getContainer();
        $hasUserEntity = $container->has(UserEntityFactory::class);
        $hasAdminEntity = $container->has(AdminEntityFactory::class);
        if ($hasUserEntity && $hasAdminEntity) {
            return $this;
        }
        if ($container instanceof SystemContainerInterface) {
            if (!$hasUserEntity) {
                $container->set(UserEntityFactory::class, UserEntityFactory::class);
            }
            if (!$hasUserEntity) {
                $container->set(AdminEntityFactory::class, AdminEntityFactory::class);
            }
            return $this;
        }
        if (!$hasUserEntity && method_exists($container, 'set')) {
            try {
                $container->set(
                    UserEntityFactory::class,
                    fn() => ContainerHelper::resolveCallable(UserEntityFactory::class, $container)
                );
            } catch (Throwable) {
            }
        }
        if (!$hasAdminEntity && method_exists($container, 'set')) {
            try {
                $container->set(
                    AdminEntityFactory::class,
                    fn() => ContainerHelper::resolveCallable(AdminEntityFactory::class, $container)
                );
            } catch (Throwable) {
            }
        }
        return $this;
    }

    /**
     * Get admin entity factory
     *
     * @return AdminEntityFactory
     */
    public function getAdminEntityFactory() : AdminEntityFactory
    {
        try {
            return $this
                ->createEntityFactoryContainer()
                ->getContainer()
                ->get(AdminEntityFactory::class);
        } catch (Throwable) {
            return new AdminEntityFactory($this);
        }
    }

    /**
     * Get user entity factory
     *
     * @return UserEntityFactory
     */
    public function getUserEntityFactory() : UserEntityFactory
    {
        try {
            return $this
                ->createEntityFactoryContainer()
                ->getContainer()
                ->get(UserEntityFactory::class);
        } catch (Throwable) {
            return new UserEntityFactory(
                $this
            );
        }
    }

    /**
     * Set as admin mode
     *
     * @return void
     */
    public function setAsAdminMode(): void
    {
        $this->currentMode = self::ADMIN_MODE;
    }

    /**
     * Set as user mode
     *
     * @return void
     */
    public function setAsUserMode(): void
    {
        $this->currentMode = self::USER_MODE;
    }

    /**
     * @param Admin|null $admin
     * @return $this
     */
    public function setAdminAccount(?Admin $admin): static
    {
        $this->adminAccount = $admin;
        return $this;
    }

    /**
     * Restore admin account
     *
     * @return $this
     */
    public function restoreAdminAccount() : static
    {
        $this->setUserAccount($this->doProcessAuth()->currentAdminAccount);
        return $this;
    }

    /**
     * @param ?User $user
     * @return $this
     */
    public function setUserAccount(?User $user): static
    {
        $this->userAccount = $user;
        return $this;
    }

    /**
     * Restore user account
     *
     * @return $this
     */
    public function restoreUserAccount(): static
    {
        $this->setUserAccount($this->doProcessAuth()->currentUserAccount);
        return $this;
    }

    /**
     * Filter uri path
     *
     * @param string|ServerRequestInterface|UriInterface|null $path default null as request uri path
     * @return string
     */
    abstract public function filterUriPath(string|ServerRequestInterface|UriInterface|null $path = null): string;

    /**
     * @return string<self::USER_MODE|self::ADMIN_MODE>
     */
    public function getCurrentMode(): string
    {
        if (!$this->currentMode) {
            $path = $this->filterUriPath($this->request);
            if ($path === UserDashboard::prefix() || str_starts_with($path, UserDashboard::prefix().'/')) {
                $this->setAsUserMode();
            } else {
                $this->setAsAdminMode();
            }
        }

        return $this->currentMode??$this->getDefaultMode();
    }

    /**
     * Check if current mode is admin mode
     *
     * @return bool
     */
    public function isAdminMode(): bool
    {
        return $this->getCurrentMode() === self::ADMIN_MODE;
    }

    /**
     * Check if current mode is user mode
     *
     * @return bool
     */
    public function isUserMode(): bool
    {
        return $this->getCurrentMode() === self::USER_MODE;
    }

    /**
     * @return self::USER_MODE|self::ADMIN_MODE|null
     */
    public function getCurrentSetMode(): ?string
    {
        return $this->currentMode;
    }

    /**
     * @return self::USER_MODE|self::ADMIN_MODE
     */
    public function getDefaultMode(): string
    {
        return $this->getDefaultMode();
    }

    /**
     * Set default mode
     *
     * @param string<"user"|"admin"> $mode
     * @return void
     * @uses self::USER_MODE
     * @uses self::ADMIN_MODE
     */
    public function setDefaultMode(string $mode): void
    {
        if ($mode === self::ADMIN_MODE || $mode === self::USER_MODE) {
            $this->defaultMode = $mode;
        }
    }

    /**
     * @return bool
     */
    public function isLoggedIn() : bool
    {
        return $this->getAccount() !== null;
    }

    /**
     * Get account
     *
     * @return User|Admin|null
     */
    public function getAccount() : User|Admin|null
    {
        return match ($this->getCurrentMode()) {
            self::ADMIN_MODE => $this->getAdminAccount(),
            self::USER_MODE => $this->getUserAccount(),
            default => null
        };
    }

    /**
     * @return Admin|User|null
     */
    public function getUserOrAdminAccountLoggedIn(): Admin|User|null
    {
        if ($this->isUserMode()) {
            return $this->getUserAccount();
        }
        return $this->getAdminAccount();
    }

    /**
     * Get role
     *
     * @return RoleInterface
     */
    public function getRole() : RoleInterface
    {
        $role = $this->getAccount()?->getObjectRole();
        if (!$role) {
            $role = ($this->defaultRole ??= RoleStatic::getUnknownRole());
        }
        return $role;
    }

    /**
     * @return ?User
     */
    public function getUserAccount(): ?User
    {
        return $this->userAccount??$this->doProcessAuth()->userAccount;
    }

    /**
     * @return ?Admin
     */
    public function getAdminAccount(): ?Admin
    {
        return $this->adminAccount??$this->doProcessAuth()->adminAccount;
    }

    /**
     * @return array{
     *      user: array{name:string, lifetime: int, wildcard: bool},
     *      admin: array{name:string, lifetime: int, wildcard: bool}
     * }
     */
    public function getCookieNames(): array
    {
        return $this->resolveCookieName()->cookieNames;
    }

    /**
     * @param string $type
     * @return ?array{name:string, lifetime: int, wildcard: bool}
     */
    public function getCookieNameData(string $type): ?array
    {
        return $this->getCookieNames()[$type]??null;
    }

    /**
     * Send auth cookie
     *
     * @param AbstractUser $userEntity
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function sendAuthCookie(
        AbstractUser $userEntity,
        ResponseInterface $response
    ) : ResponseInterface {
        $this->assertObjectCoreModule();
        $container = $this->getContainer();
        $userAuth = ContainerHelper::service(UserAuth::class, $container);

        if (!$userAuth instanceof UserAuth) {
            throw new RuntimeException(
                'Can not determine use auth object'
            );
        }
        $cookieName = $userEntity instanceof Admin
            ? 'admin'
            : ($userEntity instanceof User ? 'user' : null);
        $settings = $cookieName ? $this->getCookieNameData($cookieName) : null;
        if ($settings === null) {
            throw new RuntimeException(
                'Can not determine cookie type'
            );
        }
        $request = $this->request??ServerRequest::fromGlobals(
            ContainerHelper::use(
                ServerRequestFactory::class,
                $container
            ),
            ContainerHelper::use(
                StreamFactoryInterface::class,
                $container
            )
        );
        $domain = $request->getUri()->getHost();
        $newDomain = $this->getManager()?->dispatch(
            'auth.cookieDomain',
            $domain
        );

        $domain = is_string($newDomain) && filter_var(
            $newDomain,
            FILTER_VALIDATE_DOMAIN
        ) ? $newDomain : $domain;

        if ($settings['wildcard']) {
            $domain = DataNormalizer::splitCrossDomain($domain);
        }
        $cookie = new SetCookie(
            name: $settings['name'],
            value: $userAuth->getHashIdentity()->generate($userEntity->getId()),
            expiresAt: $settings['lifetime'] === 0 ? 0 : $settings['lifetime'] + time(),
            path: '/',
            domain: $domain
        );
        $cookieObject = $this->getManager()?->dispatch(
            'auth.cookieObject',
            $cookie
        );
        $cookie = $cookieObject instanceof SetCookie
            ? $cookieObject
            : $cookie;
        return $cookie->appendToResponse($response);
    }

    public function isAdminLoggedIn(): bool
    {
        return $this->getAdminAccount() !== null;
    }

    public function isUserLoggedIn(): bool
    {
        return $this->getUserAccount() !== null;
    }

    /**
     * @param int $id
     * @return ?Admin
     */
    public function getAdminById(int $id): ?UserEntityInterface
    {
        return $this->getAdminEntityFactory()->findById($id);
    }

    /**
     * @param string $username
     * @return ?Admin
     */
    public function getAdminByUsername(string $username): ?UserEntityInterface
    {
        return $this->getAdminEntityFactory()->findByUsername($username);
    }

    /**
     * @param string $email
     * @return ?UserEntityInterface
     */
    public function getAdminByEmail(string $email): ?UserEntityInterface
    {
        return $this->getAdminEntityFactory()->findByEmail($email);
    }

    /**
     * @param int $id
     * @return ?User
     */
    public function getUserById(int $id): ?UserEntityInterface
    {
        return $this->getUserEntityFactory()->findById($id);
    }

    /**
     * @param string $username
     * @return ?User
     */
    public function getUserByUsername(string $username) : ?UserEntityInterface
    {
        return $this->getUserEntityFactory()->findByUsername($username);
    }

    /**
     * @param string $email
     * @return ?UserEntityInterface
     */
    public function getUserByEmail(string $email) : ?UserEntityInterface
    {
        return $this->getUserEntityFactory()->findByEmail($email);
    }
}
