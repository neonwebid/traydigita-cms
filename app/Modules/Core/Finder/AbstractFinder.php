<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Finder;

use ArrayAccess\TrayDigita\App\Modules\Core\Core;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Site;
use ArrayAccess\TrayDigita\Database\Result\AbstractRepositoryFinder;

abstract class AbstractFinder extends AbstractRepositoryFinder
{
    protected ?Site $site = null;

    public function __construct(public readonly Core $core)
    {
        parent::__construct($core->getConnection());
        $this->setSite($core->getSite()?->current());
    }


    /**
     * @param ?Site $site
     * @return void
     */
    public function setSite(?Site $site) : void
    {
        $this->site = $site;
    }

    /**
     * @return ?Site
     */
    public function getSite() : ?Site
    {
        return $this->site;
    }
}
