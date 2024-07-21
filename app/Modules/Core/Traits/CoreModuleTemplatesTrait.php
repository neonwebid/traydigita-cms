<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Traits;

use ArrayAccess\TrayDigita\App\Modules\Core\Templates\TemplateRule;
use ArrayAccess\TrayDigita\Kernel\Interfaces\KernelInterface;
use ArrayAccess\TrayDigita\Templates\Wrapper;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;

trait CoreModuleTemplatesTrait
{
    use CoreModuleAssertionTrait;

    /**
     * @param $module
     * @param KernelInterface $kernel
     * @return mixed
     */
    private function eventInitModuleTemplate($module, KernelInterface $kernel) : mixed
    {
        $this->assertObjectCoreModule();

        if ($kernel->getConfigError()) {
            return $module;
        }

        $view = $this->getView();
        if (!$view) {
            return $module;
        }
        $templateRule = $view->getTemplateRule();
        $wrapper = $templateRule?->getWrapper()
            ??ContainerHelper::service(Wrapper::class, $this->getContainer());
        if (!$wrapper) {
            return $module;
        }
        $templateRule = $templateRule instanceof TemplateRule
            ? $templateRule
            : new TemplateRule($wrapper);
        $templateRule->initialize();
        $view->setTemplateRule($templateRule);
        return $module;
    }
}
