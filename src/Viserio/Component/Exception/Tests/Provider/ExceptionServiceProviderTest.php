<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Debug\ExceptionHandler as ExceptionHandlerContract;
use Viserio\Component\Contract\Exception\ExceptionInfo as ExceptionInfoContract;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonApiDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\SymfonyDisplayer;
use Viserio\Component\Exception\Displayer\ViewDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\Exception\Filter\CanDisplayFilter;
use Viserio\Component\Exception\Filter\ContentTypeFilter;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\Exception\Handler;
use Viserio\Component\Exception\Provider\ExceptionServiceProvider;
use Viserio\Component\Filesystem\Provider\FilesServiceProvider;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Log\Provider\LoggerServiceProvider;
use Viserio\Component\View\Provider\ViewServiceProvider;

class ExceptionServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new ExceptionServiceProvider());
        $container->register(new ConfigServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new FilesServiceProvider());
        $container->register(new LoggerServiceProvider());
        $container->register(new HttpFactoryServiceProvider());
        $container->get(RepositoryContract::class)->setArray(['viserio' => [
                'exception' => [
                    'env'               => 'dev',
                    'debug'             => false,
                    'default_displayer' => '',
                ],
                'view' => [
                    'paths' => [],
                ],
                'log' => [
                    'env' => 'dev',
                ],
            ],
        ]);

        self::assertInstanceOf(ExceptionInfo::class, $container->get(ExceptionInfoContract::class));

        self::assertInstanceOf(HtmlDisplayer::class, $container->get(HtmlDisplayer::class));
        self::assertInstanceOf(JsonDisplayer::class, $container->get(JsonDisplayer::class));
        self::assertInstanceOf(JsonApiDisplayer::class, $container->get(JsonApiDisplayer::class));
        self::assertInstanceOf(SymfonyDisplayer::class, $container->get(SymfonyDisplayer::class));
        self::assertInstanceOf(ViewDisplayer::class, $container->get(ViewDisplayer::class));
        self::assertInstanceOf(WhoopsDisplayer::class, $container->get(WhoopsDisplayer::class));

        self::assertInstanceOf(VerboseFilter::class, $container->get(VerboseFilter::class));
        self::assertInstanceOf(CanDisplayFilter::class, $container->get(CanDisplayFilter::class));
        self::assertInstanceOf(ContentTypeFilter::class, $container->get(ContentTypeFilter::class));

        self::assertInstanceOf(Handler::class, $container->get(Handler::class));
        self::assertInstanceOf(Handler::class, $container->get(ExceptionHandlerContract::class));
    }
}
