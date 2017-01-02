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
use Viserio\WebProfiler\TemplateManager;
use Viserio\WebProfiler\WebProfiler;
use PHPUnit\Framework\TestCase;

class WebProfilerMiddlewareTest extends TestCase
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
        $assets   = new AssetsRenderer();
        $profiler = new WebProfiler($assets);

        $profiler->enable();

        $middleware = new WebProfilerMiddleware($profiler);
        $template   = new TemplateManager(
            [],
            $profiler->getTemplate(),
            '12213435415',
            $assets->getIcons()
        );

        $renderedContent = $assets->render() . $template->render();

        $request = (new ServerRequestFactory())->createServerRequest($_SERVER);

        $response = $middleware->process($request, new DelegateMiddleware(function ($request) {
            return (new ResponseFactory())->createResponse(200);
        }));

        static::assertEquals(
            $this->removeId($renderedContent),
            $this->removeId((string) $response->getBody())
        );
    }

    private function removeId(string $html): string
    {
        return trim(
            str_replace(
                "\r\n",
                '',
                preg_replace('/="webprofiler-(.*?)"/', '', $html)
            )
        );
    }
}
