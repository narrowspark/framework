<?php
declare(strict_types=1);
namespace Narrowspark\Benchmark\Http;

use Nyholm\Psr7\Response;

class NyholmHttpResponseBench extends AbstractHttpResponseBenchCase
{
    public function classSetUp(): void
    {
        $this->response = new Response();
    }
}
