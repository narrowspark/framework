<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Fixture;

use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;

class RouteRegistrarControllerFixture
{
    public function index()
    {
        return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                    ->createStream('controller')
            );
    }

    public function destroy()
    {
        return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                    ->createStream('deleted')
            );
    }
}
