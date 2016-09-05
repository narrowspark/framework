<?php
declare(strict_types=1);
namespace Viserio\Exception\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Config\Manager as ConfigManagerContract;
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
use Viserio\Exception\Transformers\CommandLineTransformer;

class ExceptionServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ExceptionIdentifier::class => [self::class, 'createExceptionIdentifier'],
            ExceptionInfo::class => [self::class, 'createExceptionInfo'],
            Handler::class => [self::class, 'createExceptionHandler'],
            HandlerContract::class => function (ContainerInterface $container) {
                return $container->get(Handler::class);
            },
            HtmlDisplayer::class => [self::class, 'createHtmlDisplayer'],
            JsonDisplayer::class => [self::class, 'createJsonDisplayer'],
            ViewDisplayer::class => [self::class, 'createViewDisplayer'],
            WhoopsDisplayer::class => [self::class, 'createWhoopsDisplayer'],
            VerboseFilter::class => [self::class, 'createVerboseFilter'],
            CanDisplayFilter::class => [self::class, 'createCanDisplayFilter'],
            CommandLineTransformer::class => [self::class, 'createCommandLineTransformer'],
        ];
    }

    public static function createExceptionIdentifier(): ExceptionIdentifier
    {
        return new ExceptionIdentifier();
    }

    public static function createExceptionInfo(): ExceptionInfo
    {
        return new ExceptionInfo();
    }

    public static function createExceptionHandler(ContainerInterface $container): Handler
    {
        return new Handler($container);
    }

    public static function createHtmlDisplayer(ContainerInterface $container): HtmlDisplayer
    {
        return new HtmlDisplayer($container->get(ExceptionInfo::class), __DIR__ . '/../Resources/error.html');
    }

    public static function createJsonDisplayer(ContainerInterface $container): JsonDisplayer
    {
        return new JsonDisplayer($container->get(ExceptionInfo::class));
    }

    public static function createViewDisplayer(ContainerInterface $container): ViewDisplayer
    {
        return new ViewDisplayer($container->get(ExceptionInfo::class, $container->get(FactoryContract::class)));
    }

    public static function createWhoopsDisplayer(): WhoopsDisplayer
    {
        return new WhoopsDisplayer();
    }

    public static function createCommandLineTransformer(): CommandLineTransformer
    {
        return new CommandLineTransformer();
    }

    public static function createVerboseFilter(ContainerInterface $container): VerboseFilter
    {
        return new VerboseFilter($container->get(ConfigManagerContract::class)->get('exception.debug', false));
    }

    public static function createCanDisplayFilter(): CanDisplayFilter
    {
        return new CanDisplayFilter();
    }
}
