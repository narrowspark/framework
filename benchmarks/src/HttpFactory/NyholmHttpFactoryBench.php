<?php
declare(strict_types=1);
namespace Narrowspark\Benchmark\HttpFactory;

use Nyholm\Psr7\Factory\Psr17Factory;

class NyholmHttpFactoryBench extends AbstractHttpFactoryBenchCase
{
    public function classSetUp(): void
    {
        $factory = new Psr17Factory();

        $this->requestFactory  = $factory;
        $this->responseFactory = $factory;
        $this->streamFactory   = $factory;
        $this->uriFactory      = $factory;
    }
}
