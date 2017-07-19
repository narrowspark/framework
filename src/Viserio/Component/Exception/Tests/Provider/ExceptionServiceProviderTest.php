<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Exception\ExceptionInfo as ExceptionInfoContract;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\ViewDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\Exception\Filter\CanDisplayFilter;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\Exception\Handler;
use Viserio\Component\Exception\Provider\ExceptionServiceProvider;
use Viserio\Component\Exception\Transformer\ClassNotFoundFatalErrorTransformer;
use Viserio\Component\Exception\Transformer\CommandLineTransformer;
use Viserio\Component\Exception\Transformer\UndefinedFunctionFatalErrorTransformer;
use Viserio\Component\Exception\Transformer\UndefinedMethodFatalErrorTransformer;
use Viserio\Component\Filesystem\Provider\FilesServiceProvider;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
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
            ],
        ]);

        self::assertInstanceOf(ClassNotFoundFatalErrorTransformer::class, $container->get(ClassNotFoundFatalErrorTransformer::class));
        self::assertInstanceOf(CommandLineTransformer::class, $container->get(CommandLineTransformer::class));
        self::assertInstanceOf(UndefinedFunctionFatalErrorTransformer::class, $container->get(UndefinedFunctionFatalErrorTransformer::class));
        self::assertInstanceOf(UndefinedMethodFatalErrorTransformer::class, $container->get(UndefinedMethodFatalErrorTransformer::class));
        self::assertInstanceOf(ExceptionInfo::class, $container->get(ExceptionInfoContract::class));
        self::assertInstanceOf(HtmlDisplayer::class, $container->get(HtmlDisplayer::class));
        self::assertInstanceOf(JsonDisplayer::class, $container->get(JsonDisplayer::class));
        self::assertInstanceOf(ViewDisplayer::class, $container->get(ViewDisplayer::class));
        self::assertInstanceOf(WhoopsDisplayer::class, $container->get(WhoopsDisplayer::class));
        self::assertInstanceOf(VerboseFilter::class, $container->get(VerboseFilter::class));
        self::assertInstanceOf(CanDisplayFilter::class, $container->get(CanDisplayFilter::class));
        self::assertInstanceOf(Handler::class, $container->get(Handler::class));
    }
}
