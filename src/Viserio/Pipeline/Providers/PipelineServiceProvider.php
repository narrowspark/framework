<?php
declare(strict_types=1);
namespace Viserio\Pipeline\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Pipeline\Pipeline as PipelineContract;
use Viserio\Pipeline\Pipeline;

class PipelineServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Pipeline::class => [self::class, 'createPipeline'],
            PipelineContract::class => function (ContainerInterface $container) {
                return $container->get(Pipeline::class);
            },
            'pipeline' => function (ContainerInterface $container) {
                return $container->get(Pipeline::class);
            },
        ];
    }

    public static function createPipeline(ContainerInterface $container): Pipeline
    {
        return (new Pipeline())->setContainer($container);
    }
}
