<?php
declare(strict_types=1);
namespace Viserio\Component\Pipeline\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Pipeline\Pipeline as PipelineContract;
use Viserio\Component\Pipeline\Pipeline;

class PipelineServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            PipelineContract::class => [self::class, 'createPipeline'],
            Pipeline::class         => function (ContainerInterface $container) {
                return $container->get(PipelineContract::class);
            },
            'pipeline' => function (ContainerInterface $container) {
                return $container->get(PipelineContract::class);
            },
        ];
    }

    public static function createPipeline(ContainerInterface $container): Pipeline
    {
        return (new Pipeline())->setContainer($container);
    }
}
