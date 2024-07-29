<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\User;
use ArrayAccess\TrayDigita\App\Modules\Core\Exceptions\ForbiddenException;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\Dashboard as DashboardAttribute;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\User as UserAttribute;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers\Interfaces\BaseControllerInterface;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers\Interfaces\HasCapabilityInterface;
use ArrayAccess\TrayDigita\App\Modules\Core\Static\CoreModuleStatic;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\CapabilityInterface;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\PermissionInterface;
use ArrayAccess\TrayDigita\Auth\Roles\Interfaces\RoleInterface;
use ArrayAccess\TrayDigita\Routing\AbstractController as CoreAbstractController;
use ArrayAccess\TrayDigita\Traits\Service\TranslatorTrait;
use ArrayAccess\TrayDigita\Util\Filter\DataNormalizer;
use ArrayAccess\TrayDigita\View\Interfaces\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

abstract class AbstractController extends CoreAbstractController implements BaseControllerInterface
{
    use TranslatorTrait;

    /**
     * @var string $authPath
     */
    protected string $authPath = '/auth';

    /**
     * @var string $userAuthPath
     */
    protected string $userAuthPath;

    /**
     * @var string $dashboardAuthPath
     */
    protected string $dashboardAuthPath;

    /**
     * @var ?string $authenticationMethod
     */
    protected ?string $authenticationMethod = null;

    final public function beforeDispatch(ServerRequestInterface $request, string $method, ...$arguments)
    {
        $this->authPath = '/'.trim(DataNormalizer::normalizeUnixDirectorySeparator($this->authPath), '/');
        $this->authPath = $this->authPath ?: '/auth';
        $this->userAuthPath = UserAttribute::path($this->authPath);
        $this->dashboardAuthPath = DashboardAttribute::path($this->authPath);
        $mode = $this->getControllerUserMode();
        if ($mode === Core::ADMIN_MODE) {
            $this->getControllerCoreModule()->setAsAdminMode();
        } elseif ($mode === Core::USER_MODE) {
            $this->getControllerCoreModule()->setAsUserMode();
        }

        $response = $this->doBeforeDispatch($request, $method, ...$arguments);
        if ($response !== null) {
            return $response;
        }
        if (!$this->controllerPermitted()) {
            throw new ForbiddenException($request);
        }
        return null;
    }

    public function getView(): ?ViewInterface
    {
        $view = parent::getView();
        $view->setParameter('current_user', $this->getCurrentUser());
        $view->setParameter('user_account', $this->getControllerCoreModule()->getUserAccount());
        $view->setParameter('admin_account', $this->getControllerCoreModule()->getAdminAccount());
        $view->setParameter('is_login', $this->getControllerCoreModule()->isLoggedIn());
        return $view;
    }

    /**
     * @return Admin|User|null
     */
    public function getCurrentUser() : Admin|User|null
    {
        return $this->getControllerUserMode() === Core::ADMIN_MODE
            ? $this->getControllerCoreModule()->getUserAccount()
            : $this->getControllerCoreModule()->getAdminAccount();
    }

    /**
     * @return ?string
     */
    protected function getAuthenticationMethod() : ?string
    {
        return $this->authenticationMethod;
    }

    public function getControllerRole(): ?RoleInterface
    {
        return $this->getCurrentUser()?->getObjectRole()??$this->getControllerCoreModule()->getRole();
    }

    public function getControllerCoreModule(): Core
    {
        try {
            $core = $this->getModule(Core::class);
        } catch (Throwable) {
            $core = CoreModuleStatic::core();
        }
        return $core instanceof Core ? $core : CoreModuleStatic::core();
    }

    /**
     * @inheritdoc
     */
    public function getControllerPermission(): PermissionInterface
    {
        return $this->getControllerCoreModule()->getPermission();
    }

    /**
     * @return bool
     */
    public function controllerPermitted(): bool
    {
        if (!$this instanceof HasCapabilityInterface) {
            return true;
        }
        $role = $this->getControllerRole();
        if (!$role) {
            return false;
        }
        $permission = $this->getControllerPermission();
        $capabilities = $this->getControllerCapabilities();
        if (empty($capabilities)) {
            return false;
        }
        $capabilities = !is_array($capabilities) ? [$capabilities] : $capabilities;
        foreach ($capabilities as $capability) {
            if (!$capability instanceof CapabilityInterface) {
                continue;
            }
            if ($permission->permitted($role, $capability)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array|CapabilityInterface|CapabilityInterface[]
     */
    public function getControllerCapabilities(): array|CapabilityInterface
    {
        return [];
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function getControllerUserMode(): string
    {
        $authMethod = $this->getAuthenticationMethod();
        if ($authMethod === Core::ADMIN_MODE) {
            return Core::ADMIN_MODE;
        }
        if ($authMethod === Core::USER_MODE) {
            return Core::USER_MODE;
        }
        return $this->getControllerCoreModule()->getCurrentMode();
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $method
     * @param ...$arguments
     */
    public function doBeforeDispatch(
        ServerRequestInterface $request,
        string $method,
        ...$arguments
    ) {
        // pass
    }
}
