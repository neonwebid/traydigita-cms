<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Controllers;

use ArrayAccess\TrayDigita\App\Modules\Core\Depends\PostLoop;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers\AbstractController;
use ArrayAccess\TrayDigita\App\Modules\Core\Route\Controllers\Interfaces\PostsModeControllerInterface;
use ArrayAccess\TrayDigita\Routing\Attributes\Any;
use ArrayAccess\TrayDigita\Routing\Attributes\Group;
use ArrayAccess\TrayDigita\Util\Filter\ContainerHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Controller : Home
 */
#[Group('')] // route group prefix
class Home extends AbstractController implements PostsModeControllerInterface
{
    protected string $postMode = PostLoop::MODE_HOMEPAGE;

    /**
     * Do routing for any(/?)
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array<string> $parameters
     * @param string $prefixSlash
     * @param string $suffixSlash
     * @return ResponseInterface
     */
    #[Any('/(?:page[/]+(?P<page>:num:))?')]
    public function main(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $parameters,
        string $prefixSlash,
        string $suffixSlash
    ) : ResponseInterface {
        $parameters['page'] ??= 1;
        $this->getPostLoop()->setPage((int) ($parameters['page'] ?? 1));
       // example
        $stream = ContainerHelper::use(StreamFactoryInterface::class, $this->getContainer())
            ->createStream('Welcome to The Home');
        // do job task with response
        return $this->render('templates/home', [
            'title' => 'Home',
            'content' => $stream
        ]);
    }
}
