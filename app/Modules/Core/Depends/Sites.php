<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Depends;

use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Site;
use ArrayAccess\TrayDigita\Database\Helper\Expression;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use function is_string;
use function strtolower;

class Sites extends AbstractRepositoryUserDepends
{
    private ?bool $multisite = null;

    private Site|null|false $site = null;

    /**
     * @return ObjectRepository<Site>&Selectable<Site>
     */
    public function getRepository() : ObjectRepository&Selectable
    {
        return $this
            ->core
            ->getConnection()
            ->getRepository(Site::class);
    }

    /**
     * @return bool
     */
    public function isMultiSite() : bool
    {
        if ($this->multisite !== null) {
            return $this->multisite;
        }

        /**
         * @var ?\ArrayAccess\TrayDigita\App\Modules\Core\Entities\Options $obj
         * @noinspection PhpFullyQualifiedNameUsageInspection
         */
        $obj = $this
            ->core
            ->getOption()
            ->getRepository()
            ->findOneBy([
                'name' => 'enable_multisite',
                'site_id' => null
            ]);
        if (!$obj) {
            return $this->multisite = false;
        }
        $value = $obj->getValue();
        $value = is_string($value)
            ? strtolower($value)
            : $value;
        return $this->multisite = $value === 'yes'
            || $value === 'true'
            || $value === true
            || $value === '1';
    }

    /**
     * @return ?Site
     */
    public function current(): ?Site
    {
        if ($this->site !== null) {
            return $this->site?:null;
        }

        if (!$this->isMultiSite()) {
            return $this->site = new SingleSiteEntity(
                $this->core->getRequest()
            );
        }

        [$main, $alias, $host] = SingleSiteEntity::parseHost($this->core->getRequest());
        $expression = [
            Expression::eq('domain', $host)
        ];
        if ($main && $alias) {
            $expression[] = Expression::andX(
                Expression::eq('domain', $main),
                Expression::eq('domain_alias', $alias),
            );
        }
        $where = count($expression) > 1
            ? Expression::orX(...$expression)
            : $expression[0];
        $this->site = $this
            ->getRepository()
            ->matching(
                Expression::criteria()
                    ->where($where)
                    ->setMaxResults(1)
            )->first()?:false;
        return $this->site?:null;
    }
}
