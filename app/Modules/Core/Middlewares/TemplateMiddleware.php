<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Middlewares;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\AbstractCoreMiddleware;
use ArrayAccess\TrayDigita\App\Modules\Core\Templates\TemplateRule;
use ArrayAccess\TrayDigita\Util\Filter\Consolidation;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use function is_string;

class TemplateMiddleware extends AbstractCoreMiddleware
{
    /**
     * Priority should be lower than template loader
     * @var int
     */
    protected int $priority = InitMiddlewares::DEFAULT_PRIORITY - 1; // make lower than init

    /**
     * @throws Throwable
     */
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
        if (!$template) {
            return $request;
        }
        $this->core->getView()->setViewsDirectory([]);
        if ($template->getBasePath() !== $active && $option) {
            $option->set(TemplateRule::ACTIVE_TEMPLATE_KEY, $template->getBasePath(), true);
        }
        $templateName = $templateRule->getTemplateLoad();
        if ($templateName) {
            $file = $template->getTemplateDirectory() . DIRECTORY_SEPARATOR . $templateName;
            if (is_file($file)) {
                try {
                    (fn($file) => include_once $file)->call($template, $file);
                    $this->getManager()->dispatch(
                        'templates.templateFileLoaded',
                        $template,
                        $file
                    );
                } catch (Throwable $e) {
                    $logger = ContainerHelper::use(
                        LoggerInterface::class,
                        $this->getContainer()
                    );
                    $logger->notice($e, context: ['mode' => 'templates_include']);
                    throw $e;
                }
            }
        }
        return $request;
    }
}
