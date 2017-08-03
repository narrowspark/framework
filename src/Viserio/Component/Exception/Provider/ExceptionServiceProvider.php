<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Provider;

use Interop\Container\ServiceProvider;
use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Viserio\Component\Contracts\Exception\ExceptionInfo as ExceptionInfoContract;
use Viserio\Component\Contracts\Exception\Handler as HandlerContract;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\ViewDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\Exception\Filter\CanDisplayFilter;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\Exception\Handler;
use Viserio\Component\Exception\Transformer\ClassNotFoundFatalErrorTransformer;
use Viserio\Component\Exception\Transformer\CommandLineTransformer;
use Viserio\Component\Exception\Transformer\UndefinedFunctionFatalErrorTransformer;
use Viserio\Component\Exception\Transformer\UndefinedMethodFatalErrorTransformer;

class ExceptionServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ExceptionInfoContract::class                  => [self::class, 'createExceptionInfo'],
            HandlerContract::class                        => [self::class, 'createExceptionHandler'],
            Handler::class                                => function (ContainerInterface $container) {
                return $container->get(HandlerContract::class);
            },
            ExceptionHandlerContract::class               => function (ContainerInterface $container) {
                return $container->get(HandlerContract::class);
            },
            HtmlDisplayer::class                          => [self::class, 'createHtmlDisplayer'],
            JsonDisplayer::class                          => [self::class, 'createJsonDisplayer'],
            ViewDisplayer::class                          => [self::class, 'createViewDisplayer'],
            WhoopsDisplayer::class                        => [self::class, 'createWhoopsDisplayer'],
            VerboseFilter::class                          => [self::class, 'createVerboseFilter'],
            CanDisplayFilter::class                       => [self::class, 'createCanDisplayFilter'],
            ClassNotFoundFatalErrorTransformer::class     => function () {
                return new ClassNotFoundFatalErrorTransformer();
            },
            CommandLineTransformer::class                 => function () {
                return new CommandLineTransformer();
            },
            UndefinedFunctionFatalErrorTransformer::class => function () {
                return new UndefinedFunctionFatalErrorTransformer();
            },
            UndefinedMethodFatalErrorTransformer::class   => function () {
                return new UndefinedMethodFatalErrorTransformer();
            },
        ];
    }

    /**
     * Create a new ExceptionInfo instance.
     *
     * @return \Viserio\Component\Contracts\Exception\ExceptionInfo
     */
    public static function createExceptionInfo(): ExceptionInfoContract
    {
        return new ExceptionInfo();
    }

    /**
     * Create a new Handler instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contracts\Exception\Handler
     */
    public static function createExceptionHandler(ContainerInterface $container): HandlerContract
    {
        return new Handler($container);
    }

    /**
     * Create a new HtmlDisplayer instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Exception\Displayer\HtmlDisplayer
     */
    public static function createHtmlDisplayer(ContainerInterface $container): HtmlDisplayer
    {
        return new HtmlDisplayer(
            $container->get(ExceptionInfoContract::class),
            $container->get(ResponseFactoryInterface::class),
            $container
        );
    }

    /**
     * Create a new JsonDisplayer instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Exception\Displayer\JsonDisplayer
     */
    public static function createJsonDisplayer(ContainerInterface $container): JsonDisplayer
    {
        return new JsonDisplayer(
            $container->get(ExceptionInfoContract::class),
            $container->get(ResponseFactoryInterface::class)
        );
    }

    /**
     * Create a new ViewDisplayer instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Exception\Displayer\ViewDisplayer
     */
    public static function createViewDisplayer(ContainerInterface $container): ViewDisplayer
    {
        return new ViewDisplayer(
            $container->get(ExceptionInfoContract::class),
            $container->get(ResponseFactoryInterface::class),
            $container->get(FactoryContract::class)
        );
    }

    /**
     * Create a new WhoopsDisplayer instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Exception\Displayer\WhoopsDisplayer
     */
    public static function createWhoopsDisplayer(ContainerInterface $container): WhoopsDisplayer
    {
        return new WhoopsDisplayer($container->get(ResponseFactoryInterface::class));
    }

    /**
     * Create a new VerboseFilter instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Exception\Filter\VerboseFilter
     */
    public static function createVerboseFilter(ContainerInterface $container): VerboseFilter
    {
        return new VerboseFilter($container);
    }

    /**
     * Create a new CanDisplayFilter instance.
     *
     * @return \Viserio\Component\Exception\Filter\CanDisplayFilter
     */
    public static function createCanDisplayFilter(): CanDisplayFilter
    {
        return new CanDisplayFilter();
    }
}
