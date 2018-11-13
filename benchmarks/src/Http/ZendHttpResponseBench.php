<?php
declare(strict_types=1);
namespace Narrowspark\Benchmark\Http;

use Zend\Diactoros\Response;

class ZendHttpResponseBench extends AbstractHttpResponseBenchCase
{
    public function classSetUp(): void
    {
        $this->response = new Response();
    }
}
