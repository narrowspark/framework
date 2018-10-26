<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Test\Middleware;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Middleware\RequestHandlerMiddleware;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\Profiler\AssetsRenderer;
use Viserio\Component\Profiler\Middleware\ProfilerMiddleware;
use Viserio\Component\Profiler\TemplateManager;
use Viserio\Component\Profiler\Tests\Fixture\ProfilerTester;

/**
 * @internal
 */
final class ProfilerMiddlewareTest extends MockeryTestCase
{
    public function testProcess(): void
    {
        $assets   = new AssetsRenderer();
        $profiler = new ProfilerTester($assets);

        $profiler->enable();

        $middleware = new ProfilerMiddleware(new ArrayContainer([ProfilerContract::class => $profiler]));
        $template   = new TemplateManager(
            [],
            $profiler->getTemplate(),
            '12213435415',
            $assets->getIcons()
        );

        $renderedContent = $assets->render() . $template->render();

        $response = $middleware->process(new ServerRequest('/'), new RequestHandlerMiddleware(function () {
            $response = (new ResponseFactory())->createResponse();

            return $response->withHeader('content-type', 'text/html; charset=utf-8');
        }));

        $this->assertEquals(
            $this->removeId($renderedContent),
            $this->removeId((string) $response->getBody())
        );
        $this->assertRegExp('/^\d+.\d+ms$/', $response->getHeaderLine('x-response-time'));
    }

    private function removeId(string $html): string
    {
        return \trim(
            \str_replace(
                "\r\n",
                '',
                \preg_replace('/="profiler-(.*?)"/', '', $html)
            )
        );
    }
}
