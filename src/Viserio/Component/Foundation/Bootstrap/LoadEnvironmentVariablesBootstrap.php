<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Foundation\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Exception\InvalidPathException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Viserio\Bridge\Dotenv\Env;
use Viserio\Contract\Foundation\Bootstrap as BootstrapContract;
use Viserio\Contract\Foundation\Exception\RuntimeException;
use Viserio\Contract\Foundation\Kernel as KernelContract;

class LoadEnvironmentVariablesBootstrap implements BootstrapContract
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
    public static function isSupported(KernelContract $kernel): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        static::checkForSpecificEnvironmentFile($kernel, Env::get('APP_ENV'));

        $output = (new ConsoleOutput())->getErrorOutput();

        try {
            static::createDotenv($kernel)->safeLoad();

            $kernel->detectEnvironment(static function () {
                $appEnv = Env::get('APP_ENV');

                if ($appEnv === null) {
                    throw new RuntimeException('[APP_ENV] environment variable is not defined.');
                }

                return $appEnv;
            });
            $kernel->detectDebugMode(static function () {
                $appDebug = Env::get('APP_DEBUG');

                if ($appDebug === null) {
                    throw new RuntimeException('[APP_DEBUG] environment variable is not defined.');
                }

                return $appDebug;
            });
        } catch (RuntimeException $exception) {
            $output->writeln($exception->getMessage());
            $output->writeln('You need to define environment variables in your [.env] file to run the Narrowspark Framework.');

            die(1);
        } catch (InvalidPathException $exception) {
        } catch (InvalidFileException $exception) {
            $output->writeln('The environment file is invalid!');
            $output->writeln($exception->getMessage());

            die(1);
        }
    }

    /**
     * Create a Dotenv instance.
     *
     * @param KernelContract $kernel
     *
     * @return \Dotenv\Dotenv
     */
    protected static function createDotenv(KernelContract $kernel): Dotenv
    {
        return Dotenv::create(
            $kernel->getEnvironmentPath(),
            $kernel->getEnvironmentFile(),
            Env::getFactory()
        );
    }

    /**
     * Detect if a custom environment file matching the APP_ENV exists.
     *
     * @param \Viserio\Contract\Foundation\Kernel $kernel
     * @param null|string                         $env
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
     * @param \Viserio\Contract\Foundation\Kernel $kernel
     * @param string                              $file
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
