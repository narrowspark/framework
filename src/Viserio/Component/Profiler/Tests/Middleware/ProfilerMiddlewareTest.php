<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Test\Middleware;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Profiler\AssetsRenderer;
use Viserio\Component\Profiler\Middleware\ProfilerMiddleware;
use Viserio\Component\Profiler\TemplateManager;
use Viserio\Component\Profiler\Tests\Fixture\ProfilerTester;

class ProfilerMiddlewareTest extends MockeryTestCase
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

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';

        unset($server['PHP_SELF']);

        $renderedContent = $assets->render() . $template->render();

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);

        $response = $middleware->process($request, new DelegateMiddleware(function () {
            $response = (new ResponseFactory())->createResponse();
            $response = $response->withHeader('content-type', 'text/html; charset=utf-8');

            return $response;
        }));

        self::assertEquals(
            $this->removeId($renderedContent),
            $this->removeId((string) $response->getBody())
        );
        self::assertRegExp('/^\d+.\d+ms$/', $response->getHeaderLine('x-response-time'));
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
