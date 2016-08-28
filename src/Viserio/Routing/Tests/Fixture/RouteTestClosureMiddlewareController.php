<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Fixture;

use Viserio\Http\ResponseFactory;
use Viserio\Http\StreamFactory;
use Viserio\Routing\AbstractController;

class RouteTestClosureMiddlewareController extends AbstractController
{
    public function __construct()
    {
        $this->withMiddleware(new ControllerClosureMiddleware());
    }

    public function index()
    {
        return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStreamFromString('index')
            );
    }
}
