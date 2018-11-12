<?php
declare(strict_types=1);
namespace Narrowspark\Benchmark\HttpFactory;

use Zend\Diactoros\RequestFactory;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UriFactory;

/**
 * @Groups({"zend-http", "http"}, extend=true)
 */
class ZendHttpBench extends HttpBenchCase
{
    public function init(): void
    {
        $this->requestFactory = new RequestFactory();
        $this->responseFactory = new ResponseFactory();
        $this->streamFactory = new StreamFactory();
        $this->uriFactory = new UriFactory();
    }
}
