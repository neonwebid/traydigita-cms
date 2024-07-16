<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Traits;

use ArrayAccess\TrayDigita\App\Modules\Core\Depends\Option;
use ArrayAccess\TrayDigita\App\Modules\Core\Depends\Sites;

trait CoreModuleUserDependsTrait
{
    use CoreModuleAssertionTrait;

    private ?Option $option = null;
    private ?Sites $site = null;

    public function getOption(): Option
    {
        if ($this->option) {
            return $this->option;
        }
        $this->assertObjectCoreModule();
        $this->option = new Option($this);
        return $this->option;
    }

    public function getSite(): Sites
    {
        if ($this->site) {
            return $this->site;
        }
        $this->assertObjectCoreModule();
        $this->site = new Sites($this);
        return $this->site;
    }
}
