<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Providers;

use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\ServerRequestFactory;
use Viserio\WebProfiler\AssetsRenderer;
use Viserio\WebProfiler\Middleware\WebProfilerMiddleware;
use Viserio\WebProfiler\WebProfiler;

class WebProfilerServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $profiler = new WebProfiler(new AssetsRenderer());
        $profiler->enable();
        $middleware = new WebProfilerMiddleware($profiler);

        $response = $middleware->process(
            (new ServerRequestFactory())->createServerRequest($_SERVER),
            new DelegateMiddleware(function ($request) {
                return (new ResponseFactory())->createResponse(200);
            })
        );

        $profilerResponse = $profiler->modifyResponse(
            (new ServerRequestFactory())->createServerRequest($_SERVER),
            (new ResponseFactory())->createResponse(200)
        );

        static::assertEquals(
            $this->removeId((string) $profilerResponse->getBody()),
            $this->removeId((string) $response->getBody())
        );
    }

    private function removeId(string $html): string
    {
        return trim(preg_replace('/="webprofiler-(.*?)"/', '', $html));
    }
}
