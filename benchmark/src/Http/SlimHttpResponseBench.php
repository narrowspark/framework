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

namespace Narrowspark\Benchmark\Http;

use Slim\Psr7\Response;

class SlimHttpResponseBench extends AbstractHttpResponseBenchCase
{
    public function classSetUp(): void
    {
        $this->response = new Response();
    }
}
