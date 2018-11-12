<?php
declare(strict_types=1);
namespace Narrowspark\Benchmark\HttpFactory;

use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UriFactory;

/**
 * @Groups({"slim-http", "http"}, extend=true)
 */
class SlimHttpBench extends HttpBenchCase
{
    public function init(): void
    {
        $this->requestFactory = new RequestFactory();
        $this->responseFactory = new ResponseFactory();
        $this->streamFactory = new StreamFactory();
        $this->uriFactory = new UriFactory();
    }
}
