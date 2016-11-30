<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Fixture;

use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\StreamFactory;
use Viserio\Routing\AbstractController;

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
