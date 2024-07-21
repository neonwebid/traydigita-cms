<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Static;

use ArrayAccess\TrayDigita\App\Modules\Core\Depends\Option;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Options;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Site;
use Throwable;

final class OptionStatic
{
    /**
     * @return Option
     */
    public static function option() : Option
    {
        return CoreModuleStatic::core()->getOption();
    }

    /**
     * @param Options $options
     * @return bool
     */
    public static function save(Options $options): bool
    {
        try {
            self::option()?->save($options);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Get option
     *
     * @param string $name
     * @param Site|null $site
     * @param null $siteId
     * @return Options|null
     */
    public function get(
        string $name,
        ?Site &$site = null,
        &$siteId = null
    ) : ?Options {
        return self::option()?->get($name, $site, $siteId);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param bool|null $autoload
     * @param Site|false|null $site
     * @return Options|null
     */
    public static function set(
        string $key,
        mixed $value,
        ?bool $autoload = null,
        Site|false|null $site = false
    ): ?Options {
        try {
            return self::option()?->set($key, $value, $autoload, $site);
        } catch (Throwable) {
            return null;
        }
    }
}
