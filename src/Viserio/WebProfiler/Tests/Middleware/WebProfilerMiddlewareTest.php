<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Test\Middleware;

use Mockery as Mock;
use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\ServerRequestFactory;
use Viserio\WebProfiler\AssetsRenderer;
use Viserio\WebProfiler\Middleware\WebProfilerMiddleware;
use Viserio\WebProfiler\WebProfiler;

class WebProfilerMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testProcess()
    {
        $profiler = new WebProfiler(new AssetsRenderer());
        $profiler->enable();
        $middleware = new WebProfilerMiddleware($profiler);

        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        $this->assertSame(
            $this->removeId(file_get_contents(__DIR__ . '/../Fixture/View/profiler.html')),
            $this->removeId((string) $response->getBody())
        );
    }

    private function removeId(string $html): string
    {
        return trim(preg_replace('/="webprofiler-(.*?)"/', '', $html));
    }
}
