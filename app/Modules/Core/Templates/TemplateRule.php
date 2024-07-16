<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Templates;

use ArrayAccess\TrayDigita\Templates\Abstracts\AbstractTemplateRule;

class TemplateRule extends AbstractTemplateRule
{
    public const ACTIVE_TEMPLATE_KEY = 'active_template';

    /**
     * @var array<string>
     */
    protected array $requiredFiles = [
        // base
        'base.twig',
        'maintenance.twig',

        // errors
        'errors/404.twig',
        'errors/500.twig',

        // templates
        'templates/home.twig',
        'templates/post.twig',
        'templates/page.twig',
        'templates/search.twig',
        'templates/post.twig',
        'templates/archive.twig',

        // dashboard
        'dashboard/login.twig',
        'dashboard/register.twig',
        'dashboard/reset-password.twig',

        // user
        'user/login.twig',
        'user/register.twig',
        'user/reset-password.twig',
    ];
}
