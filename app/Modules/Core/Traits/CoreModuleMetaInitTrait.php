<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Traits;

use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\Dashboard;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\DashboardAPI;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\RouteAPI;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\User;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\UserAPI;
use ArrayAccess\TrayDigita\Module\Traits\ModuleTrait;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;
use ArrayAccess\TrayDigita\Util\Filter\DataNormalizer;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

trait CoreModuleMetaInitTrait
{
    use ModuleTrait,
        CoreModuleAssertionTrait;

    /**
     * @var bool Enable cache
     */
    protected bool $enableRequestCache = false;

    /**
     * @var int Cache expire
     */
    protected int $cacheRequestExpire = 3600;

    /**
     * @var bool Cache 404 request
     */
    protected bool $enableCache404Request = false;

    /**
     * @var bool Cache json
     */
    protected bool $enableCacheAPIRequest = false;

    /**
     * eventInitModuleCache
     *
     * @return void
     */
    private function eventInitModuleMeta(): void
    {
        $this->assertObjectCoreModule();
        $env = $this->getConfigEnvironment();
        $dashboardPath = $env->get('dashboard_path');
        if (is_string($dashboardPath)) {
            $dashboardPath = $this->filterUriPath($dashboardPath);
            if ($dashboardPath) {
                Dashboard::setPrefix($dashboardPath);
            }
        }
        $userPath = $env->get('user_path');
        if (is_string($userPath)) {
            $userPath = $this->filterUriPath($userPath);
            if ($userPath) {
                User::setPrefix($userPath);
            }
        }
        $apiPath = $env->get('api_path');
        if (is_string($apiPath)) {
            $apiPath = $this->filterUriPath($apiPath);
            if ($apiPath) {
                RouteAPI::setPrefix($apiPath);
            }
        }
        $this->setEnableRequestCache($env->get('enable_cache') === true);
        $expireTime = $env->get('cache_expire');
        if (is_int($expireTime)) {
            $this->cacheRequestExpire = $expireTime;
        }
        $this->setEnableCache404Request($env->get('cache_404') === true);
        $this->setEnableCacheAPIRequest($env->get('cache_api') === true);
    }

    /**
     * @return ServerRequestInterface
     */
    abstract public function getRequest(): ServerRequestInterface;

    /**
     * @return ?CacheItemPoolInterface
     */
    public function getCache() : ?CacheItemPoolInterface
    {
        return ContainerHelper::use(CacheItemPoolInterface::class, $this->getContainer());
    }

    /**
     * Filter path of dashboard and user
     *
     * @param string|ServerRequestInterface|UriInterface|null $path default null as request
     * @return string
     */
    public function filterUriPath(string|ServerRequestInterface|UriInterface|null $path = null): string
    {
        $path ??= $this->getRequest();
        $path = $path instanceof ServerRequestInterface
            ? $path->getUri()->getPath()
            : ($path instanceof UriInterface ? $path->getPath() : $path);
        if (str_contains($path, '?')) {
            $path = explode('?', $path)[0];
        }
        if (str_contains($path, '#')) {
            $path = explode('#', $path)[0];
        }

        $path = DataNormalizer::normalizeUnixDirectorySeparator($path);
        $path = preg_replace('~[^a-z0-9/_-]~', '', strtolower($path));
        $path = trim(preg_replace('~/+~', '/', $path), '/');
        $path = trim(preg_replace('~/+~', '/', $path), '/');
        return $path ? "/$path" : '';
    }

    /**
     * @param bool $enableCache404Request
     */
    public function setEnableCache404Request(bool $enableCache404Request): void
    {
        $this->enableCache404Request = $enableCache404Request;
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return bool
     */
    public function isEnableCache404Request(?ServerRequestInterface $request = null): bool
    {
        $request ??= $this->getRequest();
        return $this->getManager()->dispatch('cacheRequest.enable404', $this->enableCache404Request, $request) === true;
    }

    /**
     * @param bool $enableCacheAPIRequest
     */
    public function setEnableCacheAPIRequest(bool $enableCacheAPIRequest): void
    {
        $this->enableCacheAPIRequest = $enableCacheAPIRequest;
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return bool
     */
    public function isEnableCacheAPIRequest(?ServerRequestInterface $request = null): bool
    {
        $request ??= $this->getRequest();
        return $this->getManager()->dispatch('cacheRequest.enableAPI', $this->enableCacheAPIRequest, $request) === true;
    }

    /**
     * @param int $cacheExpire
     * @return void
     */
    public function setCacheRequestExpire(int $cacheExpire): void
    {
        $this->cacheRequestExpire = $cacheExpire;
    }

    /**
     * Get cache expire
     *
     * @param ServerRequestInterface|null $request
     * @return int
     */
    public function getCacheRequestExpire(?ServerRequestInterface $request = null): int
    {
        $request ??= $this->getRequest();
        $expire = $this->cacheRequestExpire;
        $expire = $this->getManager()->dispatch('cacheRequest.expire', $expire, $request);
        return is_int($expire) ? $expire : $this->cacheRequestExpire;
    }

    /**
     * @param bool $enableCache
     * @return void
     */
    public function setEnableRequestCache(bool $enableCache): void
    {
        $this->enableRequestCache = $enableCache;
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return bool
     */
    public function isEnableRequestCache(?ServerRequestInterface $request = null): bool
    {
        $request ??= $this->getRequest();
        return ($this->getManager()->dispatch('cacheRequest.enable', $this->enableRequestCache, $request) === true);
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return bool
     */
    public function isUserPath(?ServerRequestInterface $request = null): bool
    {
        $request ??= $this->getRequest();
        $path = $this->filterUriPath($request->getUri()->getPath());
        return $path === User::path() || str_starts_with($path, User::path() . '/');
    }

    public function isUserAPIPath(?ServerRequestInterface $request = null): bool
    {
        $request ??= $this->getRequest();
        $path = $this->filterUriPath($request->getUri()->getPath());
        return UserAPI::prefix() === $path || str_starts_with($path, UserAPI::prefix() . '/');
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return bool
     */
    public function isDashboardPath(?ServerRequestInterface $request = null): bool
    {
        $request ??= $this->getRequest();
        $path = $this->filterUriPath($request->getUri()->getPath());
        return $path === Dashboard::path() || str_starts_with($path, Dashboard::path() . '/');
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return bool
     */
    public function isDashboardAPIPath(?ServerRequestInterface $request = null): bool
    {
        $request ??= $this->getRequest();
        $path = $this->filterUriPath($request->getUri()->getPath());
        return DashboardAPI::prefix() === $path || str_starts_with($path, DashboardAPI::prefix() . '/');
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return bool
     */
    public function isRouteAPIPath(?ServerRequestInterface $request = null): bool
    {
        $request ??= $this->getRequest();
        $path = $this->filterUriPath($request->getUri()->getPath());
        return RouteAPI::prefix() === $path || str_starts_with($path, RouteAPI::prefix() . '/');
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return bool
     */
    public function isAPIPath(?ServerRequestInterface $request = null): bool
    {
        $request ??= $this->getRequest();
        return $this->isRouteAPIPath($request) || $this->isUserAPIPath($request) || $this->isDashboardAPIPath($request);
    }
}
