<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Exception\InvalidPathException;
use Symfony\Component\Console\Input\ArgvInput;
use Viserio\Component\Contract\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Support\Debug\Dumper;
use Viserio\Component\Support\Env;

class LoadEnvironmentVariables implements BootstrapContract
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
    public static function bootstrap(KernelContract $kernel): void
    {
        if (\file_exists($kernel->getStoragePath('config.cache.php'))) {
            return;
        }

        $env = Env::get('APP_ENV');

        if ($env === null && ! class_exists(Dotenv::class)) {
            $message = '[APP_ENV] environment variable is not defined. ';
            $message .= 'You need to define environment variables for configuration or run [composer require vlucas/phpdotenv] as a Composer dependency to load variables from a .env file.';

            throw new \RuntimeException($message);
        }

        if (! class_exists(Dotenv::class)) {
            $kernel->detectEnvironment(function () use ($env) {
                return $env ?? 'prod';
            });

            return;
        }

        static::checkForSpecificEnvironmentFile($kernel, $env);

        try {
            (new Dotenv($kernel->getEnvironmentPath(), $kernel->getEnvironmentFile()))->load();

            $kernel->detectEnvironment(function () use ($env) {
                return $env ?? Env::get('APP_ENV', 'prod');
            });
        } catch (InvalidPathException $exception) {
        } catch (InvalidFileException $exception) {
            Dumper::dump($exception->getMessage());
            die(1);
        }
    }

    /**
     * Detect if a custom environment file matching the APP_ENV exists.
     *
     * @param \Viserio\Component\Contract\Foundation\Kernel $kernel
     * @param null|string                                   $env
     *
     * @return void
     */
    protected static function checkForSpecificEnvironmentFile(KernelContract $kernel, ?string $env): void
    {
        if ($kernel->isRunningInConsole() && ($input = new ArgvInput())->hasParameterOption(['--env', '-e'])) {
            if (static::setEnvironmentFilePath(
                $kernel,
                $kernel->getEnvironmentFile() . '.' . $input->getParameterOption(['--env', '-e'])
            )) {
                return;
            }
        }

        if ($env === null) {
            return;
        }

        static::setEnvironmentFilePath($kernel, $kernel->getEnvironmentFile() . '.' . $env);
    }

    /**
     * Load a custom environment file.
     *
     * @param \Viserio\Component\Contract\Foundation\Kernel $kernel
     * @param string                                        $file
     *
     * @return bool
     */
    protected static function setEnvironmentFilePath(KernelContract $kernel, string $file): bool
    {
        if (\file_exists($kernel->getEnvironmentPath() . \DIRECTORY_SEPARATOR . $file)) {
            $kernel->loadEnvironmentFrom($file);

            return true;
        }

        return false;
    }
}
