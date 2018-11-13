<?php
declare(strict_types=1);
namespace Narrowspark\Benchmark\Http;

use Slim\Psr7\Response;

class SlimHttpResponseBench extends AbstractHttpResponseBenchCase
{
    public function classSetUp(): void
    {
        $this->response = new Response();
    }
}
