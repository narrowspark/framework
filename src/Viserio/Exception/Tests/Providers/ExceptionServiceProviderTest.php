<?php
declare(strict_types=1);
namespace Viserio\Exception\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Events\Dispatcher;
use Viserio\Exception\Providers\ExceptionServiceProvider;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Contracts\Exception\Handler as HandlerContract;
use Viserio\Contracts\View\Factory as FactoryContract;
use Viserio\Exception\Displayers\HtmlDisplayer;
use Viserio\Exception\Displayers\JsonDisplayer;
use Viserio\Exception\Displayers\ViewDisplayer;
use Viserio\Exception\Displayers\WhoopsDisplayer;
use Viserio\Exception\ExceptionIdentifier;
use Viserio\Exception\ExceptionInfo;
use Viserio\Exception\Filters\CanDisplayFilter;
use Viserio\Exception\Filters\VerboseFilter;
use Viserio\Exception\Handler;
use Viserio\Filesystem\Providers\FilesServiceProvider;
use Viserio\View\Providers\ViewServiceProvider;
use Viserio\Exception\Transformers\CommandLineTransformer;

class ExceptionServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ExceptionServiceProvider());
        $container->register(new ConfigServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new FilesServiceProvider());

        $this->assertInstanceOf(ExceptionIdentifier::class, $container->get(ExceptionIdentifier::class));
        $this->assertInstanceOf(ExceptionInfo::class, $container->get(ExceptionInfo::class));
        $this->assertInstanceOf(HtmlDisplayer::class, $container->get(HtmlDisplayer::class));
        $this->assertInstanceOf(JsonDisplayer::class, $container->get(JsonDisplayer::class));
        $this->assertInstanceOf(ViewDisplayer::class, $container->get(ViewDisplayer::class));
        $this->assertInstanceOf(WhoopsDisplayer::class, $container->get(WhoopsDisplayer::class));
        $this->assertInstanceOf(VerboseFilter::class, $container->get(VerboseFilter::class));
        $this->assertInstanceOf(CanDisplayFilter::class, $container->get(CanDisplayFilter::class));
        $this->assertInstanceOf(CommandLineTransformer::class, $container->get(CommandLineTransformer::class));
        $this->assertInstanceOf(Handler::class, $container->get(Handler::class));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new ExceptionServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new FilesServiceProvider());

        $container->instance('options', [
            'debug' => true,
        ]);

        $this->assertInstanceOf(VerboseFilter::class, $container->get(VerboseFilter::class));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new ExceptionServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new FilesServiceProvider());

        $container->instance('viserio.exception.options', [
            'debug' => true,
        ]);

        $this->assertInstanceOf(VerboseFilter::class, $container->get(VerboseFilter::class));
    }
}
