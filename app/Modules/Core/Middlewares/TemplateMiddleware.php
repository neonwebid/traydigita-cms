<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Middlewares;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\AbstractCoreMiddleware;
use ArrayAccess\TrayDigita\App\Modules\Core\Templates\TemplateRule;
use ArrayAccess\TrayDigita\Util\Filter\Consolidation;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function is_string;

class TemplateMiddleware extends AbstractCoreMiddleware
{
    /**
     * Priority should be lower than template loader
     * @var int
     */
    protected int $priority = InitMiddlewares::DEFAULT_PRIORITY - 1; // make lower than init

    protected function doProcess(ServerRequestInterface $request): ServerRequestInterface|ResponseInterface
    {
        $templateRule = $this->core->getView()?->getTemplateRule();
        if (!$templateRule) {
            return $request;
        }

        if (Consolidation::isCli()) {
            return $request;
        }

        $option = $this->core->getOption();
        $active = $option->get(TemplateRule::ACTIVE_TEMPLATE_KEY)?->getValue();
        if (is_string($active)) {
            $templateRule->setActive($active);
        }
        $template = $templateRule->getActive();
        if ($template) {
            if ($template->getBasePath() !== $active && $option) {
                $option->set(TemplateRule::ACTIVE_TEMPLATE_KEY, $template->getBasePath(), true);
            }
            $this->core->getView()->setViewsDirectory([]);
        }
        return $request;
    }
}
