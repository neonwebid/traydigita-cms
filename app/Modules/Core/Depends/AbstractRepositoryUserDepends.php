<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Depends;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\Container\Interfaces\ContainerAllocatorInterface;
use ArrayAccess\TrayDigita\Traits\Container\ContainerAllocatorTrait;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;

abstract class AbstractRepositoryUserDepends implements ContainerAllocatorInterface
{
    use ContainerAllocatorTrait;

    public function __construct(public Core $core)
    {
        $container = $core->getContainer();
        if ($container) {
            $this->setContainer($container);
        }
    }

    /**
     * @return ObjectRepository&Selectable
     */
    abstract public function getRepository() : ObjectRepository&Selectable;
}
