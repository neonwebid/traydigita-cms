<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Depends;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;

abstract class AbstractRepositoryUserDepends
{
    public function __construct(public Core $core)
    {
    }

    /**
     * @return ObjectRepository&Selectable
     */
    abstract public function getRepository() : ObjectRepository&Selectable;
}
