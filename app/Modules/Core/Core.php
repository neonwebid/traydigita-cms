<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core;

use ArrayAccess\TrayDigita\App\Modules\Core\Abstracts\AbstractCoreMiddleware;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Admin;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\AdminLog;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\AdminMeta;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\AdminOnlineActivity;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Attachment;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Capability;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Options;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Role;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\RoleCapability;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Site;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\User;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\UserAttachment;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\UserLog;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\UserMeta;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\UserOnlineActivity;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\UserTerm;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\UserTermGroup;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\UserTermGroupMeta;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\UserTermMeta;
use ArrayAccess\TrayDigita\App\Modules\Core\Traits\CoreModuleTemplatesTrait;
use ArrayAccess\TrayDigita\App\Modules\Core\Traits\CoreModuleUserAuthTrait;
use ArrayAccess\TrayDigita\App\Modules\Core\Traits\CoreModuleUserDependsTrait;
use ArrayAccess\TrayDigita\App\Modules\Core\Traits\CoreModuleUserEventTrait;
use ArrayAccess\TrayDigita\App\Modules\Core\Traits\CoreModuleUserPermissiveTrait;
use ArrayAccess\TrayDigita\Database\Connection;
use ArrayAccess\TrayDigita\Http\ServerRequest;
use ArrayAccess\TrayDigita\Module\AbstractModule;
use ArrayAccess\TrayDigita\Traits\Database\ConnectionTrait;
use ArrayAccess\TrayDigita\Traits\Service\TranslatorTrait;
use ArrayAccess\TrayDigita\Traits\View\ViewTrait;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;
use ArrayAccess\TrayDigita\Util\Filter\IterableHelper;
use ArrayAccess\TrayDigita\View\Engines\TwigEngine;
use ArrayAccess\TrayDigita\View\Twig\TwigExtensions\AbstractExtension;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use function array_flip;
use function array_map;
use function strtolower;
use const PHP_INT_MIN;

final class Core extends AbstractModule
{
    use TranslatorTrait,
        ViewTrait,
        CoreModuleUserDependsTrait,
        ConnectionTrait,
        CoreModuleTemplatesTrait,
        CoreModuleUserEventTrait,
        CoreModuleUserAuthTrait,
        CoreModuleUserPermissiveTrait;

    /**
     * @var string
     */
    protected string $name = 'Core';

    /**
     * @var bool
     */
    protected bool $important = true;

    /**
     * @var int -> very important
     */
    protected int $priority = PHP_INT_MIN;

    /**
     * @var bool
     */
    private bool $didInit = false;


    final public const ENTITY_CHECKING = [
        'required' => [
            Admin::class,
            AdminLog::class,
            AdminMeta::class,
            AdminOnlineActivity::class,
            Attachment::class,
            Capability::class,
            Options::class,
            Role::class,
            RoleCapability::class,
            Site::class,
            User::class,
            UserAttachment::class,
            UserLog::class,
            UserMeta::class,
            UserOnlineActivity::class,
            UserTerm::class,
            UserTermGroup::class,
            UserTermGroupMeta::class,
            UserTermMeta::class,
        ],
        'optional' => [
        ],
        'additional' => [
        ]
    ];


    /**
     * @var array<string, int>
     */
    private array $priorities = [];

    /**
     * @var ?Connection
     */
    private ?Connection $connection = null;

    /**
     * @var ServerRequestInterface|null $request the server request
     */
    private ?ServerRequestInterface $request = null;

    /**
     * @var ?array
     */
    private ?array $entityChecking = null;

    /**
     * @var bool $middlewareRegistered
     */
    private bool $middlewareRegistered = false;

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->translateContext(
            'Core',
            'module',
            'module'
        );
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->translateContext(
            'Main core module',
            'module',
            'module'
        );
    }

    /**
     * @return array{
     *     required: array<string, bool>,
     *     optionsl: array<string, bool>,
     *     additional: array<string, bool>,
     *     tables: array<string, string>,
     * }
     * @throws \Doctrine\DBAL\Exception
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public function checkEntity(): array
    {
        if ($this->entityChecking !== null) {
            return $this->entityChecking;
        }

        $em = $this->getEntityManager();
        $tables = $this->getConnection()->createSchemaManager()->listTableNames();
        $tables = array_flip(array_map('strtolower', $tables));
        $this->entityChecking = [];
        foreach (self::ENTITY_CHECKING as $type => $entities) {
            $this->entityChecking[$type] = [];
            foreach ($entities as $entity) {
                $table = $em->getClassMetadata($entity)->getTableName();
                $this->entityChecking[$type][$entity] = isset($tables[strtolower($table)]);
                $this->entityChecking['tables'][$entity] = $table;
            }
        }

        return $this->entityChecking;
    }

    /**
     * Register middlewares
     *
     * @return void
     */
    private function registerMiddlewares() : void
    {
        if ($this->middlewareRegistered) {
            return;
        }
        $this->middlewareRegistered = true;
        $directory = __DIR__ . DIRECTORY_SEPARATOR . 'Middlewares';
        IterableHelper::each(
            Finder::create()
                ->in($directory)
                ->ignoreVCS(true)
                ->ignoreDotFiles(true)
                ->depth('<=10')
                ->name('/^[_A-za-z]([a-zA-Z0-9]+)?\.php$/')
                ->files(),
            function ($key, SplFileInfo $info) use ($directory) {
                $namespace = __NAMESPACE__ .'\\Middlewares\\';
                $baseDir = substr($info->getPath(), strlen($directory));
                $namespace .= str_replace(DIRECTORY_SEPARATOR, '\\', $baseDir);
                $className = $namespace . $info->getBasename('.php');
                if (!class_exists($className)
                    || !is_subclass_of($className, AbstractCoreMiddleware::class)) {
                    return;
                }
                $this
                    ->getKernel()
                    ->getHttpKernel()
                    ->addDeferredMiddleware(new $className($this));
            }
        );
    }

    /**
     * Register twig extensions
     *
     * @return void
     */
    private function registerTwigExtensions() : void
    {
        $engine = $this->getView()?->getEngine('twig');
        if (!$engine instanceof TwigEngine) {
            return;
        }
        $directory = __DIR__ . DIRECTORY_SEPARATOR . 'TwigExtensions';
        IterableHelper::each(
            Finder::create()
                ->in($directory)
                ->ignoreVCS(true)
                ->ignoreDotFiles(true)
                ->depth('<=10')
                ->name('/^[_A-za-z]([a-zA-Z0-9]+)?\.php$/')
                ->files(),
            function ($key, SplFileInfo $info) use ($directory, $engine) {
                $namespace = __NAMESPACE__ .'\\TwigExtensions\\';
                $baseDir = substr($info->getPath(), strlen($directory));
                $namespace .= str_replace(DIRECTORY_SEPARATOR, '\\', $baseDir);
                $className = $namespace . $info->getBasename('.php');
                if (!class_exists($className) || !is_subclass_of($className, AbstractExtension::class)) {
                    return;
                }
                $engine->addExtension(new $className($engine));
            }
        );
    }

    /**
     * @inheritdoc
     */
    protected function doInit(): void
    {
        if ($this->didInit) {
            return;
        }

        $this->didInit = true;
        $this->registerMiddlewares();
        $this->registerTwigExtensions();
        if ($this->getKernel()->getConfigError()) {
            return;
        }

        $this->getConnection()->registerEntityDirectory(__DIR__ . '/Entities');
        $this->getManager()->attachOnce('view.beforeRender', [$this, 'eventViewBeforeRender']);
        $this->getManager()->attachOnce('view.bodyAttributes', [$this, 'eventViewBodyAttributes']);
        $this->getManager()->attachOnce('kernel.afterInitModules', [$this, 'eventInitModule']);
    }

    /**
     * Get request
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request ??= ServerRequest::fromGlobals(
            ContainerHelper::use(ServerRequestFactoryInterface::class, $this->getContainer()),
            ContainerHelper::use(StreamFactoryInterface::class, $this->getContainer())
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return void
     */
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }
}
