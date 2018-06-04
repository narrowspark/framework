<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Fixture;

use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;

class InvokableActionFixture
{
    public function __invoke()
    {
        return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                    (new StreamFactory())
                        ->createStream('Hallo')
                );
    }
}
