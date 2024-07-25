<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Finder;

use ArrayAccess\TrayDigita\App\Modules\Core\Entities\Site;
use ArrayAccess\TrayDigita\App\Modules\Core\Entities\User;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use function is_int;
use function is_string;

class UserFinder extends AbstractFinder
{
    protected ?string $columnSearch = 'username';

    /**
     * @return ObjectRepository&Selectable<User>
     */
    public function getRepository() : ObjectRepository&Selectable
    {
        return $this->connection->getRepository(
            User::class
        );
    }

    public function find($id) : ?User
    {
        if (is_int($id)) {
            return $this->findById($id);
        }
        if (is_string($id)) {
            return $this->findByUsername($id);
        }
        return null;
    }

    public function findById(int $id) : ?User
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param string $username
     * @param int|Site|null $site
     * @return User|null
     */
    public function findByUsername(string $username, int|Site|null $site = null) : ?User
    {
        if (func_num_args() < 2) {
            $site ??= $this->getSite();
        }
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

    public function findByEmail(string $email, int|Site|null $site = null) : ?User
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
