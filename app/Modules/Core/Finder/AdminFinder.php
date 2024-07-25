<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Finder;

use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Site;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Admin;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use function is_int;
use function is_string;

class AdminFinder extends AbstractFinder
{
    protected ?string $columnSearch = 'username';

    /**
     * @return ObjectRepository&Selectable<Admin>
     */
    public function getRepository() : ObjectRepository&Selectable
    {
        return $this->connection->getRepository(
            Admin::class
        );
    }

    public function find($id) : ?Admin
    {
        if (is_int($id)) {
            return $this->findById($id);
        }
        if (is_string($id)) {
            return $this->findByUsername($id);
        }
        return null;
    }

    public function findById(int $id) : ?Admin
    {
        return $this->getRepository()->find($id);
    }

    public function findByUsername(string $username, int|Site|null $site = null) : ?Admin
    {
        if (func_num_args() < 2) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        return $this
            ->getRepository()
            ->findOneBy([
                'username' => $username,
                'site_id' => $site
            ]);
    }

    public function findByEmail(string $email, int|Site|null $site = null) : ?Admin
    {
        if (func_num_args() < 2) {
            $site ??= $this->getSite();
        }
        $site = $site instanceof Site ? $site->getId() : $site;
        return $this
            ->getRepository()
            ->findOneBy([
                'email' => $email,
                'site_id' => $site
            ]);
    }
}
