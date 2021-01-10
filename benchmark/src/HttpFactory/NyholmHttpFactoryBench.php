<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
