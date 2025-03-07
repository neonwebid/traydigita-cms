<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Depends;

use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Options;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Site;
use ArrayAccess\TrayDigita\Database\Helper\Expression;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use function array_filter;
use function array_merge;
use function array_values;
use function is_int;
use function is_numeric;
use function is_string;
use function str_contains;

class Option extends AbstractRepositoryUserDepends
{
    /**
     * @var array<string, Options>
     */
    private array $deferred = [];

    /**
     * Get entity manager
     *
     * @return EntityManagerInterface
     */
    public function getEntityManager() : EntityManagerInterface
    {
        return $this->core->getEntityManager();
    }

    /**
     * Determine site
     *
     * @param $site
     * @param $argumentValid
     * @return Site|null
     */
    public function determineSite($site, &$argumentValid = null) : ?Site
    {
        if ($site instanceof Site) {
            $argumentValid = true;
            if ($site->isPostLoad()) {
                return $site?:null;
            }
            $site = $site->getId();
            if (!is_int($site)) {
                return null;
            }
        }

        $argumentValid = false;
        if ($site === null) {
            $argumentValid = true;
            return $this->core->getSite()->current();
        }
        if (is_numeric($site)) {
            $site = is_string($site)
                && !str_contains($site, '.')
                ? (int) $site
                : $site;
            $argumentValid = is_int($site);
            return $argumentValid ? $this
                ->core
                ->getConnection()
                ->findOneBy(
                    Site::class,
                    ['id' => $site]
                ) : null;
        }
        if (is_string($site)) {
            $argumentValid = true;
            return $this
                ->core
                ->getConnection()
                ->findOneBy(
                    Site::class,
                    ['domain' => $site]
                );
        }

        return null;
    }

    /**
     * @return ObjectRepository<Options>&Selectable<Options>
     */
    public function getRepository() : ObjectRepository&Selectable
    {
        return $this
            ->core
            ->getConnection()
            ->getRepository(Options::class);
    }

    /**
     * Get batch of options
     *
     * @param string $name
     * @param Site|null $site
     * @param string ...$optionNames
     * @return array
     */
    public function getBatch(
        string $name,
        ?Site &$site = null,
        string ...$optionNames
    ): array {
        $site = $this->determineSite($site);
        $siteId = $site?->getId();
        $optionNames = array_merge([$name], $optionNames);
        $optionNames = array_filter($optionNames, 'is_string');
        return $this
            ->getRepository()
            ->findBy(
                [
                    'name' => Expression::in('name', array_values($optionNames)),
                    'site_id' => $siteId
                ]
            );
    }

    /**
     * @param Site|int|null $site
     * @return int|null
     */
    private function normalizeSiteId(Site|int|null $site = null) : ?int
    {
        $site ??= $this->core->getSite();
        $site = $site instanceof Site ? $site->getId() : $site;
        return is_int($site) ? $site : null;
    }

    /**
     * @param string $name
     * @param Site|false|null $site
     * @param $siteId
     * @return Options|null
     */
    public function getOrCreate(
        string $name,
        Site|false|null &$site = false,
        &$siteId = null
    ): ?Options {
        $site = $this->normalizeSiteId($site);
        $option = $this->get($name, $site, $siteId);
        $siteId = !is_int($siteId) ? null : $siteId;
        if (!$option) {
            $option = new Options();
            $option->setName($name);
            $option->setSiteId($siteId);
            $option->setEntityManager($this->getEntityManager());
        }
        return $option;
    }

    /**
     * Save batch of options
     *
     * @param Options ...$option
     * @return void
     */
    public function saveBatch(
        Options ...$option
    ): void {
        $em = null;
        foreach ($option as $opt) {
            $em ??= $opt->getEntityManager()??$this->getEntityManager();
            $em->persist($opt);
        }
        $em?->flush();
    }

    /**
     * Get option
     *
     * @param string $name
     * @param Site|null $site
     * @param null $siteId
     * @return Options|null
     */
    public function get(
        string $name,
        ?Site &$site = null,
        &$siteId = null
    ) : ?Options {
        $site = $this->determineSite($site);
        $siteId = $site?->getId();
        return $this
            ->getRepository()
            ->findOneBy([
                'name' => $name,
                'site_id' => $siteId
            ]);
    }

    /**
     * Save option
     *
     * @param Options $options
     * @return void
     */
    public function save(Options $options): void
    {
        $em = $options->getEntityManager()??$this->getEntityManager();
        $em->persist($options);
        $em->flush();
    }

    /**
     * Set option
     *
     * @param string $name
     * @param mixed $value
     * @param bool|null $autoload
     * @param Site|false|null $site
     * @return Options
     */
    public function set(
        string $name,
        mixed $value,
        ?bool $autoload = null,
        Site|false|null $site = false
    ): Options {
        $entity = $this->getOrCreate($name, $site);
        $entity->setValue($value);
        if ($autoload !== null) {
            $entity->setAutoload($autoload);
        }
        $this->save($entity);

        return $entity;
    }

    /**
     * Save deferred
     *
     * @param Options $options
     * @param Options ...$anotherOptions
     * @return void
     */
    public function saveDeferred(Options $options, Options ...$anotherOptions): void
    {
        $this->deferred[$options->getName()] = $options;
        foreach ($anotherOptions as $opt) {
            $this->deferred[$opt->getName()] = $opt;
        }
    }

    /**
     * Cancel deferred
     *
     * @param string ...$names the option name
     * @return void
     */
    public function cancelDeferred(string|Options ...$names): void
    {
        if (empty($names)) {
            $this->deferred = [];
            return;
        }
        foreach ($names as $name) {
            if ($name instanceof Options) {
                $name = $name->getName();
            }
            unset($this->deferred[$name]);
        }
    }

    /**
     * Commit deferred, save all deferred options
     *
     * @return void
     */
    public function commitDeferred(): void
    {
        $this->saveBatch(...array_values($this->deferred));
        $this->deferred = [];
    }
}
