<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Fixture;

use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\AbstractController;

class RouteTestClosureMiddlewareController extends AbstractController
{
    public function __construct()
    {
        $this->withMiddleware(ControllerClosureMiddleware::class);
    }

    public function index()
    {
        return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                    ->createStream('index')
            );
    }
}
