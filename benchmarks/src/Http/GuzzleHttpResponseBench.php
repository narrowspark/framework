<?php
declare(strict_types=1);
namespace Narrowspark\Benchmark\Http;

use GuzzleHttp\Psr7\Response;

class GuzzleHttpResponseBench extends AbstractHttpResponseBenchCase
{
    public function classSetUp(): void
    {
        $this->response = new Response();
    }
}
