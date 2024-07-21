<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Middlewares;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\AbstractCoreMiddleware;
use ArrayAccess\TrayDigita\App\Modules\Core\Static\CacheStatic;
use ArrayAccess\TrayDigita\Http\Code;
use ArrayAccess\TrayDigita\Http\Factory\ResponseFactory;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;
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
     * @var bool Serve from cache
     */
    private bool $serveFromCache = false;

    /**
     * @var bool Debug
     */
    private bool $debug = false;

    /**
     * @var bool $showDebugBar show the debug bar
     */
    private bool $showDebugBar = false;

    /**
     * Check if need to cache
     *
     * @param ?ServerRequestInterface $request
     * @return bool
     */
    public function isNeedToCache(?ServerRequestInterface $request = null): bool
    {
        $request ??= $this->core->getRequest();
        return !(
            $request->getMethod() !== 'GET'
            || $this->debug
            || $this->serveFromCache
            || ! $this->cacheKey
            || ! $this->core->isEnableRequestCache($request)
            || $this->core->isUserPath($request)
            || $this->core->isDashboardPath($request)
            || $this->core->isAPIPath($request)
        );
    }

    /**
     * @inheritdoc
     */
    protected function doProcess(ServerRequestInterface $request): ServerRequestInterface|ResponseInterface
    {
        $env = $this->core->getConfigEnvironment();
        $pathUri = $request->getUri()->getPath();
        $query   = $request->getUri()->getQuery();
        if ($query) {
            $pathUri .= '?' . $query;
        }
        $this->cacheKey = 'server_cache_' . sha1($pathUri);
        $this->debug = $env->get('debug') === true;
        // disable cache for dashboard and user
        if (!$this->isNeedToCache($request)) {
            return $request;
        }
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
        $expiredTime = $this->core->getCacheRequestExpire($request);
        if ($expiredTime <= 0) {
            return null;
        }
        // no cache
        /*if (str_contains($request->getHeaderLine('Cache-Control'), 'no-cache')) {
            return null;
        }*/
        try {
            $item = CacheStatic::get($this->cacheKey);
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
                || ($saved_time + $expiredTime) < time()
            ) {
                // delete cache
                CacheStatic::deleteItem($this->cacheKey);
                return null;
            }
            foreach ($headers as $name => $header) {
                if (!is_string($name) || !is_array($header)) {
                    // delete cache
                    CacheStatic::deleteItem($this->cacheKey);
                    return null;
                }
            }
            if ($code === 404 && !$this->core->isEnableCache404Request($request)) {
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

        $expiredTime = $saved_time + $expiredTime;
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
        $expiredTime = $this->core->getCacheRequestExpire($this->request);
        if ($expiredTime <= 0) {
            return $response;
        }
        $code = $response->getStatusCode();
        if (($code === 404 && !$this->core->isEnableCache404Request($this->request))
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
        $stream->rewind();
        $headers = array_change_key_case($response->getHeaders());
        // no save header of cache-control & set-cookie
        unset($headers['set-cookie'], $headers['cache-control'], $headers['content-length']);
        CacheStatic::save(
            $this->cacheKey,
            [
                'saved_time' => time(),
                'data' => $stream->getContents(),
                'headers' => $headers,
                'code' => $code,
                'reason' => $response->getReasonPhrase(),
                'content_type' => $isJson ? 'json' : 'html',
                'has_content_length' => $response->hasHeader('Content-Length')
            ],
            $expiredTime
        );
        $stream->rewind();
        return $response;
    }
}
