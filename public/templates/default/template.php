<?php
/*!
 * template.php is a template file for the default template
 * This file will automatically load when the template is active and selected
 */
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\Templates\Default;

use ArrayAccess\TrayDigita\Assets\AssetsJsCssQueue;
use ArrayAccess\TrayDigita\Templates\Template;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;

/**
 * @var Template $this
 */
if (!isset($this) || !$this instanceof Template) {
    return;
}

return (function () {
    /**
     * @param Template $activeTemplate
     */
    $this
        ->getManager()
        ?->attachOnce(
            'templates.templateFileLoaded',
            function (Template $activeTemplate): void {
                $assets = ContainerHelper::use(AssetsJsCssQueue::class, $activeTemplate->getContainer());
                if (!$assets) {
                    return;
                }
                $activeTemplate->getManager()?->attachOnce(
                    'view.contentHeader',
                    static function () use ($assets) {
                        echo $assets->renderHeader();
                    }
                );
                $activeTemplate->getManager()?->attachOnce(
                    'view.contentFooter',
                    static function () use ($assets) {
                        echo $assets->renderLastCss();
                        echo $assets->renderFooter();
                        echo $assets->renderLastScript();
                    }
                );
            }
        );
    return $this;
})();
