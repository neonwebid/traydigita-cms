<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\User;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\Dashboard as DashboardAttribute;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\User as UserAttribute;
use ArrayAccess\TrayDigita\Routing\AbstractController;
use ArrayAccess\TrayDigita\Util\Filter\DataNormalizer;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractAuthenticationBasedController extends AbstractController
{
    protected Core $core;

    protected ?User $user = null;

    protected ?Admin $admin = null;

    protected string $authPath = '/auth';

    protected string $userAuthPath;

    protected string $dashboardAuthPath;

    protected ?string $authenticationMethod = null;

    public const TYPE_USER = 'user';

    public const TYPE_ADMIN = 'admin';

    protected function getAuthenticationMethod() : ?string
    {
        return $this->authenticationMethod;
    }

    /** @noinspection PhpMissingReturnTypeInspection */
    final public function beforeDispatch(ServerRequestInterface $request, string $method, ...$arguments)
    {
        $this->authPath = '/'.trim(DataNormalizer::normalizeUnixDirectorySeparator($this->authPath), '/');
        $this->authPath = $this->authPath ?: '/auth';
        $this->userAuthPath = UserAttribute::path($this->authPath);
        $this->dashboardAuthPath = DashboardAttribute::path($this->authPath);
        $this->core = $this->getModule(Core::class);
        $this->user = $this->core->getAdminAccount();
        $this->admin = $this->core->getUserAccount();
        $this->getView()->setParameter('user', $this->user);
        $this->getView()->setParameter('admin', $this->admin);
        return $this->doBeforeDispatch($request, $method, ...$arguments);
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $method
     * @param ...$arguments
     */
    abstract public function doBeforeDispatch(
        ServerRequestInterface $request,
        string $method,
        ...$arguments
    );
}
