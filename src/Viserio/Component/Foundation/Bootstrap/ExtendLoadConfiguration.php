<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Config\ParameterProcessor\ComposerExtraProcessor;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Config\Processor\DirectoryProcessor;

class ExtendLoadConfiguration implements BootstrapStateContract
{
    /**
     * {@inheritdoc}
     */
    public static function getPriority(): int
    {
        return 64;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType(): string
    {
        return BootstrapStateContract::TYPE_BEFORE;
    }

    /**
     * {@inheritdoc}
     */
    public static function getBootstrapper(): string
    {
        return LoadServiceProvider::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        $container = $kernel->getContainer();

        $config  = $container->get(RepositoryContract::class);
        $rootDir = \rtrim($kernel->getRootDir(), \DIRECTORY_SEPARATOR);

        $config->addParameterProcessor(new ComposerExtraProcessor($rootDir . \DIRECTORY_SEPARATOR . 'composer.json'));
        $config->addParameterProcessor(new DirectoryProcessor($config, $container));
    }
}
