<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\Root\Public;

use ArrayAccess\TrayDigita\Web;
use function dirname;
use function define;
use const DIRECTORY_SEPARATOR;

// phpcs:disable PSR1.Files.SideEffects
return (function () {
    // define app directory
    define('TD_APP_DIRECTORY', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app');

    // define current index file
    define('TD_INDEX_FILE', __FILE__);

    // require autoloader
    require dirname(__DIR__) .'/vendor/autoload.php';

    // should use return to builtin web server running properly
    return Web::serve();
})();
