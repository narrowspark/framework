<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Providers;

use Interop\Container\ServiceProvider;
use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Viserio\Component\Contracts\Exception\Handler as HandlerContract;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Exception\Displayers\HtmlDisplayer;
use Viserio\Component\Exception\Displayers\JsonDisplayer;
use Viserio\Component\Exception\Displayers\ViewDisplayer;
use Viserio\Component\Exception\Displayers\WhoopsDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\Exception\Filters\CanDisplayFilter;
use Viserio\Component\Exception\Filters\VerboseFilter;
use Viserio\Component\Exception\Handler;

class ExceptionServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ExceptionInfo::class            => [self::class, 'createExceptionInfo'],
            HandlerContract::class          => [self::class, 'createExceptionHandler'],
            Handler::class                  => function (ContainerInterface $container) {
                return $container->get(HandlerContract::class);
            },
            ExceptionHandlerContract::class  => function (ContainerInterface $container) {
                return $container->get(HandlerContract::class);
            },
            HtmlDisplayer::class    => [self::class, 'createHtmlDisplayer'],
            JsonDisplayer::class    => [self::class, 'createJsonDisplayer'],
            ViewDisplayer::class    => [self::class, 'createViewDisplayer'],
            WhoopsDisplayer::class  => [self::class, 'createWhoopsDisplayer'],
            VerboseFilter::class    => [self::class, 'createVerboseFilter'],
            CanDisplayFilter::class => [self::class, 'createCanDisplayFilter'],
        ];
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
        return new HtmlDisplayer(
            $container->get(ExceptionInfo::class),
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container
        );
    }

    public static function createJsonDisplayer(ContainerInterface $container): JsonDisplayer
    {
        return new JsonDisplayer(
            $container->get(ExceptionInfo::class),
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class)
        );
    }

    public static function createViewDisplayer(ContainerInterface $container): ViewDisplayer
    {
        return new ViewDisplayer(
            $container->get(ExceptionInfo::class),
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(FactoryContract::class)
        );
    }

    public static function createWhoopsDisplayer(): WhoopsDisplayer
    {
        return new WhoopsDisplayer();
    }

    public static function createVerboseFilter(ContainerInterface $container): VerboseFilter
    {
        return new VerboseFilter($container);
    }

    public static function createCanDisplayFilter(): CanDisplayFilter
    {
        return new CanDisplayFilter();
    }
}
