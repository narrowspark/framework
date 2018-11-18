<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router\Traits;

use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;

trait TestRouter405Trait
{
    /**
     * @dataProvider routerMatching405Provider
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter405($httpMethod, $uri): void
    {
        $this->expectException(MethodNotAllowedException::class);

        $this->definitions($this->router);

        $this->router->dispatch($this->serverRequestFactory->createServerRequest($httpMethod, $uri));
    }

    abstract public function expectException(string $exception): void;
}
