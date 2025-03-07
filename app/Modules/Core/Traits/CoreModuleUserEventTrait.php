<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Traits;

use ArrayAccess\TrayDigita\Util\Filter\DataNormalizer;
use ArrayAccess\TrayDigita\View\Interfaces\ViewInterface;
use function is_array;

trait CoreModuleUserEventTrait
{
    use CoreModuleAssertionTrait;

    private function eventViewBodyAttributes($attributes): array
    {
        $this->assertObjectCoreModule();
        if (!($manager = $this->getManager())
            || !$manager->insideOf('view.bodyAttributes')
        ) {
            return $attributes;
        }

        $attributes = !is_array($attributes) ? $attributes : [];
        $attributes['class'] = DataNormalizer::splitStringToArray($attributes['class']??null)??[];
        $user = $this->getUserAccount();
        $admin = $this->getAdminAccount();
        if (!$user && !$admin) {
            return $attributes;
        }
        $attributes['data-user-logged-in'] = true;
        return $attributes;
    }

    /** @noinspection PhpUnusedParameterInspection */
    private function eventViewBeforeRender(
        $path,
        $parameters,
        ViewInterface $view
    ) {
        $this->assertObjectCoreModule();
        if (!($manager = $this->getManager())
            || !$manager->insideOf('view.beforeRender')
        ) {
            return $path;
        }
        $view->setParameter('user_user', $this->getUserAccount());
        $view->setParameter('admin_user', $this->getAdminAccount());
        return $path;
    }
}
