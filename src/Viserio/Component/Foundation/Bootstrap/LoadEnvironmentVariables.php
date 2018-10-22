<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Exception\InvalidPathException;
use Symfony\Component\Console\Input\ArgvInput;
use Viserio\Component\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Support\Debug\Dumper;
use Viserio\Component\Support\Env;

class LoadEnvironmentVariables implements BootstrapStateContract
{
    /**
     * {@inheritdoc}
     */
    public static function getPriority(): int
    {
        return 32;
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
        return LoadConfiguration::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        if (! \class_exists(Dotenv::class) || \file_exists($kernel->getStoragePath('config.cache.php'))) {
            return;
        }

        static::checkForSpecificEnvironmentFile($kernel);

        try {
            (new Dotenv($kernel->getEnvironmentPath(), $kernel->getEnvironmentFile()))->load();
        } catch (InvalidPathException $exception) {
        } catch (InvalidFileException $exception) {
            Dumper::dump($exception->getMessage());
            die();
        }
    }

    /**
     * Detect if a custom environment file matching the APP_ENV exists.
     *
     * @param \Viserio\Component\Contract\Foundation\Kernel $kernel
     *
     * @return void
     */
    protected static function checkForSpecificEnvironmentFile(KernelContract $kernel): void
    {
        if ($kernel->isRunningInConsole() && ($input = new ArgvInput())->hasParameterOption(['--env', '-e'])) {
            static::setEnvironmentFilePath(
                $kernel,
                $kernel->getEnvironmentFile() . '.' . $input->getParameterOption(['--env', '-e'])
            );
        }

        $env = Env::get('APP_ENV');

        if ($env === null) {
            return;
        }

        static::setEnvironmentFilePath(
            $kernel,
            $kernel->getEnvironmentFile() . '.' . $env
        );
    }

    /**
     * Load a custom environment file.
     *
     * @param \Viserio\Component\Contract\Foundation\Kernel $kernel
     * @param string                                        $file
     *
     * @return void
     */
    protected static function setEnvironmentFilePath(KernelContract $kernel, string $file): void
    {
        if (\file_exists($kernel->getEnvironmentPath() . \DIRECTORY_SEPARATOR . $file)) {
            $kernel->loadEnvironmentFrom($file);
        }
    }
}
