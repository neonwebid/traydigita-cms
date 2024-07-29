<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\Exceptions;

use ArrayAccess\TrayDigita\Http\Code;
use ArrayAccess\TrayDigita\Http\RequestResponseExceptions\RequestSpecializedCodeException;

class UnauthorizedException extends RequestSpecializedCodeException
{
    protected $code = Code::UNAUTHORIZED;

    /**
     * @var string
     */
    protected string $description = 'You do not have permission to access this resource.';
}
