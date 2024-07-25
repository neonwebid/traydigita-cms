<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Finder;

use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Role;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Site;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use function is_string;

class RoleFinder extends AbstractFinder
{
    protected ?string $columnSearch = 'name';

    /**
     * @return ObjectRepository&Selectable<Role>
     */
    public function getRepository() : ObjectRepository&Selectable
    {
        return $this->connection->getRepository(
            Role::class
        );
    }

    public function find($id) : ?Role
    {
        if (is_string($id)) {
            return $this->findBySlug($id);
        }
        return null;
    }

    /**
     * @param string $id
     * @return Role|null
     */
    public function findById(mixed $id) : ?Role
    {
        $id = is_scalar($id) ? (string) $id : null;
        return is_string($id) ? $this->findBySlug($id) : null;
    }

    public function findBySlug(string $slug, int|Site|null $site = null) : ?Role
    {
        if (func_num_args() < 2) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        return $this
            ->getRepository()
            ->findOneBy([
                'identity' => $slug,
                'site_id' => $site
            ]);
    }
}
