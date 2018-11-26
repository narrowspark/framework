<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router\Traits;

use Narrowspark\HttpStatus\Exception\NotFoundException;

trait TestRouter404Trait
{
    /**
     * @dataProvider routerMatching404Provider
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter404($httpMethod, $uri): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->dispatch($this->serverRequestFactory->createServerRequest($httpMethod, $uri));
    }

    abstract public function expectException(string $exception): void;
}
