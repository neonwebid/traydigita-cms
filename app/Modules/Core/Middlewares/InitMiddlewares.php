<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Middlewares;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\AbstractCoreMiddleware;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\Dashboard;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Attributes\User;
use ArrayAccess\TrayDigita\Cache\Cache;
use ArrayAccess\TrayDigita\Collection\Config;
use ArrayAccess\TrayDigita\Http\Code;
use ArrayAccess\TrayDigita\Http\Factory\ResponseFactory;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;
use ArrayAccess\TrayDigita\Util\Filter\DataNormalizer;
use ArrayAccess\TrayDigita\Util\Filter\DataType;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Middleware to initialize middlewares and serving cache
 */
class InitMiddlewares extends AbstractCoreMiddleware
{
    public const DEFAULT_PRIORITY = parent::DEFAULT_PRIORITY - 99999;

   /**
    * The middleware priorities.
    * Higher priority will call first
    */
    protected int $priority = self::DEFAULT_PRIORITY;

    /**
     * @var ?string Cache key
     */
    private ?string $cacheKey = null;

    /**
     * @var int Expired time
     */
    private int $expiredTime = 3600;

    /**
     * @var bool Serve from cache
     */
    private bool $serveFromCache = false;

    /**
     * @var bool Cache 404
     */
    private bool $cache404 = false;

    /**
     * @var bool Is member or user
     */
    private bool $isMemberOrUser = false;

    /**
     * @var bool Debug
     */
    private bool $debug = false;

    /**
     * @var bool $showDebugBar show the debug bar
     */
    private bool $showDebugBar = false;

    /**
     * Filter path of dashboard and user
     *
     * @param string $path
     * @return string
     */
    protected function filterPath(string $path): string
    {
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
     * Check if need to cache
     *
     * @param ?ServerRequestInterface $request
     * @return bool
     */
    public function isNeedToCache(?ServerRequestInterface $request): bool
    {
        // should GET
        if (!$request
            || $this->debug
            || $this->serveFromCache
            || !$this->cacheKey
            || $this->isMemberOrUser
            || $request->getMethod() !== 'GET'
        ) {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function doProcess(ServerRequestInterface $request): ServerRequestInterface|ResponseInterface
    {
        $env = ContainerHelper::use(Config::class)->get('environment');
        if (!$env instanceof Config) {
            return $request;
        }

        $dashboardPath = $env->get('dashboard_path');
        if (is_string($dashboardPath)) {
            $dashboardPath = $this->filterPath($dashboardPath);
            if ($dashboardPath) {
                Dashboard::setPrefix($dashboardPath);
            }
        }
        $userPath = $env->get('user_path');
        if (is_string($userPath)) {
            $userPath = $this->filterPath($userPath);
            if ($userPath) {
                User::setPrefix($userPath);
            }
        }
        $enableCache = $env->get('enable_cache') === true;
        if (!$enableCache) {
            return $request;
        }
        $expireTime = $env->get('cache_expire');
        if (is_int($expireTime) && $expireTime > 0) {
            $this->expiredTime = $expireTime;
        }
        $this->cache404 = $env->get('cache_404') === true;
        $this->showDebugBar = $env->get('profiling') === true && $env->get('debugBar') === true;
        // should GET
        if ($request->getMethod() !== 'GET') {
            return $request;
        }
        $path = DataNormalizer::normalizeUnixDirectorySeparator($request->getUri()->getPath());
        $dashboardPath = Dashboard::prefix();
        $userPath = User::prefix();
        $this->isMemberOrUser = $path === $dashboardPath
            || $userPath === $path
            || str_starts_with($path, $dashboardPath . '/')
            || str_starts_with($path, $userPath . '/');
        $this->cacheKey = 'server_cache_' . sha1($request->getUri()->getPath());
        // disable cache for dashboard and user
        if (!$this->isNeedToCache($request)) {
            return $request;
        }
        $this->debug = $env->get('debug') === true;
        // no cache
        if ($this->debug) {
            return $request;
        }
        $response = $this->serveCachePossible($request);
        if ($response instanceof ResponseInterface) {
            return $response;
        }
        // attach cache
        $this->getManager()?->attach('response.final', [$this, 'cacheFinal']);
        // do process middleware
        return $request;
    }

    /**
     * Serve cache if possible
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface|null
     */
    private function serveCachePossible(ServerRequestInterface $request) : ?ResponseInterface
    {
        // should GET
        if (!$this->isNeedToCache($request)) {
            return null;
        }
        // no cache
        /*if (str_contains($request->getHeaderLine('Cache-Control'), 'no-cache')) {
            return null;
        }*/
        try {
            $cache = ContainerHelper::use(Cache::class, $this->getContainer());
            $item = $cache->getItem($this->cacheKey)->get();
            if (!is_array($item)) {
                return null;
            }

            $saved_time = $item['saved_time'] ?? null;
            $data = $item['data'] ?? null;
            $headers = $item['headers'] ?? null;
            $code = $item['code'] ?? null;
            $reason = $item['reason'] ?? null;
            $contentType = $item['content_type'] ?? null;
            $hasContentLength = $item['has_content_length'] ?? null;
            if (!is_int($saved_time)
                || !is_string($data)
                || !is_array($headers)
                || !is_int($code)
                || !is_bool($hasContentLength)
                || !Code::statusMessage($code)
                || !is_string($reason)
                || !in_array($contentType, ['json', 'html'])
                || $saved_time > time()
                || ($saved_time + $this->expiredTime) < time()
            ) {
                // delete cache
                $cache->deleteItem($this->cacheKey);
                return null;
            }
            foreach ($headers as $name => $header) {
                if (!is_string($name) || !is_array($header)) {
                    // delete cache
                    $cache->deleteItem($this->cacheKey);
                    return null;
                }
            }
            if ($code === 404 && !$this->cache404) {
                // delete cache
                return null;
            }
            if ($code !== 200 && $code !== 404) {
                // delete cache
                return null;
            }
            $headerContentType = $headers['content-type'][0] ?? null;
            $headerContentType = is_string($headerContentType) ? $headerContentType : (
                $contentType === 'json' ? 'application/json' : 'text/html'
            );
            // unset content type & set cookie headers
            unset(
                $headers['content-type'],
                $headers['set-cookie'],
                $headers['cache-control'],
                $headers['content-length']
            );
            $isJson = DataType::isJsonContentType($headerContentType);
            $isHtml = DataType::isHtmlContentType($headerContentType);
            if ($isJson && $contentType !== 'json'
                || $isHtml && $contentType !== 'html'
            ) {
                return null;
            }
        } catch (Throwable) {
            return null;
        }
        $this->serveFromCache = true;
        $responseFactory = ContainerHelper::service(
            ResponseFactoryInterface::class,
            $this->getContainer()
        )??new ResponseFactory();
        $response = $responseFactory->createResponse($code, $reason);
        $response->getBody()->write($data);
        unset($data);
        foreach ($headers as $name => $header) {
            $response = $response->withHeader($name, $header);
        }
        // if has content length & no debug bar
        if ($hasContentLength && !$this->showDebugBar) {
            $response = $response->withHeader('Content-Length', $response->getBody()->getSize());
        }

        $expiredTime = $saved_time + $this->expiredTime;
        // set cache control
        return $response
            ->withHeader('Expires', gmdate('D, d M Y H:i:s T', $expiredTime))
            ->withHeader('Cache-Control', 'public, max-age=' . time() - $saved_time)
            ->withHeader('X-Cache-Status', 'HIT');
    }

    /**
     * Save cache
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @internal
     */
    private function cacheFinal(ResponseInterface $response) : ResponseInterface
    {
        if (!$this->isNeedToCache($this->request)) {
            return $response;
        }

        $code = $response->getStatusCode();
        if (($code === 404 && !$this->cache404)
            || ($code !== 200 && $code !== 404)
        ) {
            return $response;
        }

        $isJson = DataType::isJsonContentType($response);
        $isHtml = DataType::isHtmlContentType($response);
        if (!$isJson && !$isHtml) {
            return $response;
        }
        if (!$this->getManager()?->insideOf('response.final')) {
            return $response;
        }
        // detach cache
        $this->getManager()->detach('response.final', [$this, 'cacheFinal']);
        $stream = $response->getBody();
        if (!$stream->isSeekable()) {
            return $response;
        }

        try {
            $cache = ContainerHelper::use(Cache::class, $this->getContainer());
            $item = $cache->getItem($this->cacheKey);
        } catch (Throwable) {
            return $response;
        }
        $stream->rewind();
        $headers = array_change_key_case($response->getHeaders());
        // no save header of cache-control & set-cookie
        unset($headers['set-cookie'], $headers['cache-control'], $headers['content-length']);
        $item
            ->expiresAfter($this->expiredTime)
            ->set([
                'saved_time' => time(),
                'data' => $stream->getContents(),
                'headers' => $headers,
                'code' => $code,
                'reason' => $response->getReasonPhrase(),
                'content_type' => $isJson ? 'json' : 'html',
                'has_content_length' => $response->hasHeader('Content-Length')
            ]);
        $stream->rewind();
        try {
            $cache->save($item);
        } catch (Throwable) {
        }
        return $response;
    }
}
