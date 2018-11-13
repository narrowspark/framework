<?php
declare(strict_types=1);
namespace Narrowspark\Benchmark\Http;

use Zend\Diactoros\RequestFactory;
use Zend\Diactoros\Response;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UriFactory;

class ZendHttpResponseBench extends AbstractHttpResponseBenchCase
{
    public function classSetUp(): void
    {
        $this->response = new Response();
    }
}
