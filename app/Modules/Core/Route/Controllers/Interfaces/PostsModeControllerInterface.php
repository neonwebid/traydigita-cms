<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers\Interfaces;

use ArrayAccess\TrayDigita\App\Modules\Core\Depends\PostLoop;

interface PostsModeControllerInterface extends BaseControllerInterface
{
    /**
     * Get the post mode
     *
     * @return string
     */
    public function getPostMode() : string;

    /**
     * @return PostLoop
     */
    public function getPostLoop() : PostLoop;
}
