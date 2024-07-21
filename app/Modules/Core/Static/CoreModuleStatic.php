<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Static;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\Module\Modules;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;
use Throwable;

final class CoreModuleStatic
{
    /**
     * @var Core $core The core instance.
     */
    private static Core $core;

    /**
     * @var bool $lockCore Lock the core instance.
     */
    private static bool $lockCore = false;

    /**
     * Lock the core instance.
     */
    public static function lockCore(): void
    {
        self::$lockCore = true;
    }

    /**
     * Set the core instance.
     *
     * @param Core $core The core instance.
     */
    public static function setCore(Core $core): void
    {
        if (self::$lockCore && isset(self::$core)) {
            return;
        }
        self::$core = $core;
    }

    /**
     * Get the core instance.
     *
     * @return ?Core The core instance.
     */
    public static function core(): ?Core
    {
        if (!isset(self::$core)) {
            try {
                $core = ContainerHelper::use(Modules::class)?->get(Core::class);
                if ($core instanceof Core) {
                    self::$core = $core;
                }
            } catch (Throwable) {
                return null;
            }
        }
        return self::$core??null;
    }
}
