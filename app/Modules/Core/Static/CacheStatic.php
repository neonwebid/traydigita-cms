<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Static;

use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;
use DateInterval;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

final class CacheStatic
{
    /**
     * @return CacheItemPoolInterface|null
     */
    public static function cache() : ?CacheItemPoolInterface
    {
        return CoreModuleStatic::core()?->getCache()??ContainerHelper::use(CacheItemPoolInterface::class);
    }

    /**
     * @param string $key
     * @param mixed $data
     * @param DateInterval|int|null $expire
     * @return bool
     */
    public static function save(string $key, mixed $data, DateInterval|int|null $expire = null): bool
    {
        $cacheItem = self::getItem($key);
        if (!$cacheItem) {
            return false;
        }
        $cacheItem->set($data);
        $cacheItem->expiresAfter($expire);
        return self::saveItem($cacheItem);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function get(string $key): mixed
    {
        $cacheItem = self::getItem($key);
        return $cacheItem?->get();
    }

    public static function delete(string $key): bool
    {
        return self::deleteItem($key);
    }

    public static function deletes(array $keys = []): bool
    {
        return self::deleteItems($keys);
    }

    public static function saveItem(CacheItemInterface $cacheItem): bool
    {
        return self::cache()?->save($cacheItem) === true;
    }

    public static function saveDeferred(CacheItemInterface $cacheItem): bool
    {
        return self::cache()?->saveDeferred($cacheItem) === true;
    }

    public static function hasItem(string $key): bool
    {
        try {
            return self::cache()?->hasItem($key) === true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public static function getItem(string $key): ?CacheItemInterface
    {
        try {
            return self::cache()?->getItem($key);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    public static function deleteItem(string $key): bool
    {
        try {
            return self::cache()?->deleteItem($key) === true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public static function deleteItems(array $keys = []) : bool
    {
        try {
            return self::cache()?->deleteItems($keys) === true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function clear(): bool
    {
        return self::cache()?->clear() === true;
    }

    public static function commit(): bool
    {
        return self::cache()?->commit() === true;
    }
}
