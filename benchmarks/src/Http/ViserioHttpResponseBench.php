<?php
declare(strict_types=1);
namespace Narrowspark\Benchmark\Http;

use Viserio\Component\Http\Response;

class ViserioHttpResponseBench extends AbstractHttpResponseBenchCase
{
    public function classSetUp(): void
    {
        $this->response = new Response();
    }
}
