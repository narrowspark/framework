<?php
declare(strict_types=1);
namespace Narrowspark\Benchmark\HttpFactory;

use Viserio\Component\HttpFactory\RequestFactory;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\HttpFactory\UriFactory;

/**
 * @Groups({"viserio-http", "http"}, extend=true)
 */
class ViserioHttpBench extends HttpBenchCase
{
    public function init(): void
    {
        $this->requestFactory = new RequestFactory();
        $this->responseFactory = new ResponseFactory();
        $this->streamFactory = new StreamFactory();
        $this->uriFactory = new UriFactory();
    }
}
