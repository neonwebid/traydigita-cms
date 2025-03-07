<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\Root\Public;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\Exceptions\Runtime\RuntimeException;
use ArrayAccess\TrayDigita\Kernel\Decorator;
use ArrayAccess\TrayDigita\Kernel\Interfaces\KernelInterface;
use ArrayAccess\TrayDigita\Module\Modules;
use ArrayAccess\TrayDigita\Web;
use function dirname;
use function define;
use const DIRECTORY_SEPARATOR;

// phpcs:disable PSR1.Files.SideEffects
return (function () {
    // define root directory
    define('TD_ROOT_DIRECTORY', dirname(__DIR__));

    // define app directory
    define('TD_APP_DIRECTORY', TD_ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'app');

    // define current index file
    define('TD_INDEX_FILE', __FILE__);

    // define minimum kernel version
    define('TD_MINIMUM_KERNEL_VERSION', '2.0.2');

    // require autoloader
    require TD_ROOT_DIRECTORY .'/vendor/autoload.php';

    // load preload file
    if (file_exists(TD_ROOT_DIRECTORY . '/preload-index.php') && is_file(TD_ROOT_DIRECTORY . '/preload-index.php')) {
        (static function () {
            require TD_ROOT_DIRECTORY . '/preload-index.php';
        })();
    }

    // check kernel version
    Decorator::kernel()
        ->getManager()
        ?->attachOnce(
            'kernel.afterInitConfig',
            static function (KernelInterface $kernel) {
                if (version_compare($kernel::VERSION, TD_MINIMUM_KERNEL_VERSION, '<')) {
                    throw new RuntimeException(
                        sprintf(
                            'System require minimum %1$s Kernel version (%2$s), please update your %1$s version.',
                            'TrayDigita',
                            TD_MINIMUM_KERNEL_VERSION
                        )
                    );
                }
            }
        );

    // check core Module
    Decorator::kernel()
        ->getManager()
        ?->attachOnce(
            'kernel.beforeInitModules',
            static function (Modules $modules) {
                // do something before init modules
                if ($modules->has(Core::class)) {
                    return;
                }
                throw new RuntimeException(
                    'Core module not exists, please check your module configuration.'
                );
            }
        );
    // should use return to builtin web server running properly
    return Web::serve();
})();
