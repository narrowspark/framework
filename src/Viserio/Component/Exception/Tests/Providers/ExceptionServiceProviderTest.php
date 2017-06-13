<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Exception\Displayers\HtmlDisplayer;
use Viserio\Component\Exception\Displayers\JsonDisplayer;
use Viserio\Component\Exception\Displayers\ViewDisplayer;
use Viserio\Component\Exception\Displayers\WhoopsDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\Exception\Filters\CanDisplayFilter;
use Viserio\Component\Exception\Filters\VerboseFilter;
use Viserio\Component\Exception\Handler;
use Viserio\Component\Exception\Providers\ExceptionServiceProvider;
use Viserio\Component\Exception\Transformers\ClassNotFoundFatalErrorTransformer;
use Viserio\Component\Exception\Transformers\CommandLineTransformer;
use Viserio\Component\Exception\Transformers\UndefinedFunctionFatalErrorTransformer;
use Viserio\Component\Exception\Transformers\UndefinedMethodFatalErrorTransformer;
use Viserio\Component\Filesystem\Providers\FilesServiceProvider;
use Viserio\Component\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Component\View\Providers\ViewServiceProvider;

class ExceptionServiceProviderTest extends TestCase
{
    public function testProvider()
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
        self::assertInstanceOf(ExceptionInfo::class, $container->get(ExceptionInfo::class));
        self::assertInstanceOf(HtmlDisplayer::class, $container->get(HtmlDisplayer::class));
        self::assertInstanceOf(JsonDisplayer::class, $container->get(JsonDisplayer::class));
        self::assertInstanceOf(ViewDisplayer::class, $container->get(ViewDisplayer::class));
        self::assertInstanceOf(WhoopsDisplayer::class, $container->get(WhoopsDisplayer::class));
        self::assertInstanceOf(VerboseFilter::class, $container->get(VerboseFilter::class));
        self::assertInstanceOf(CanDisplayFilter::class, $container->get(CanDisplayFilter::class));
        self::assertInstanceOf(Handler::class, $container->get(Handler::class));
    }
}
