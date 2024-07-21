<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Traits;

use ArrayAccess\TrayDigita\App\Modules\Core\Depends\Option;
use ArrayAccess\TrayDigita\App\Modules\Core\Depends\Sites;

trait CoreModuleUserDependsTrait
{
    use CoreModuleAssertionTrait;

    /**
     * @var Option $option
     */
    private Option $option;

    /**
     * @var Sites $site
     */
    private Sites $site;

    /**
     * @return Option
     */
    public function getOption(): Option
    {
        if (!isset($this->option)) {
            $this->assertObjectCoreModule();
            $this->option = new Option($this);
        }
        return $this->option;
    }

    /**
     * @return Sites
     */
    public function getSite(): Sites
    {
        if (!isset($this->site)) {
            $this->assertObjectCoreModule();
            $this->site = new Sites($this);
        }
        return $this->site;
    }
}
