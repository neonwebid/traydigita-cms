<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\Root\Public;

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

    // require autoloader
    require TD_ROOT_DIRECTORY .'/vendor/autoload.php';

    // load preload file
    if (file_exists(TD_ROOT_DIRECTORY . '/preload-index.php') && is_file(TD_ROOT_DIRECTORY . '/preload-index.php')) {
        (static function () {
            require TD_ROOT_DIRECTORY . '/preload-index.php';
        })();
    }

    // should use return to builtin web server running properly
    return Web::serve();
})();
