<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Abstracts;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\Middleware\AbstractMiddleware;

abstract class AbstractCoreMiddleware extends AbstractMiddleware
{
    public const DEFAULT_PRIORITY = PHP_INT_MAX - 9999;

    protected int $priority = self::DEFAULT_PRIORITY;

    /**
     * AbstractCoreMiddleware constructor.
     *
     * @param Core $core
     */
    final public function __construct(
        public readonly Core $core
    ) {
        parent::__construct($this->core->getContainer());
        $this->onConstruct();
    }

    protected function onConstruct()
    {
        // override this method to add your own logic
    }
}
