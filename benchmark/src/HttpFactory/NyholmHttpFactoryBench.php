<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Narrowspark\Benchmark\HttpFactory;

use Nyholm\Psr7\Factory\Psr17Factory;

class NyholmHttpFactoryBench extends AbstractHttpFactoryBenchCase
{
    public function classSetUp(): void
    {
        $factory = new Psr17Factory();

        $this->requestFactory = $factory;
        $this->responseFactory = $factory;
        $this->streamFactory = $factory;
        $this->uriFactory = $factory;
    }
}
