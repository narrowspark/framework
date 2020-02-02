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

use Symfony\Component\Console\Output\ConsoleOutput;
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
        $output = (new ConsoleOutput())->getErrorOutput();

        try {
            $kernel->detectEnvironment(static function (): string {
                /** @var null|string $appEnv */
                $appEnv = ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) ?? null;

                if ($appEnv === null) {
                    throw new RuntimeException('[APP_ENV] environment variable is not defined.');
                }

                return $appEnv;
            });
        } catch (RuntimeException $exception) {
            $output->writeln($exception->getMessage());
            $output->writeln('You need to define environment variables in your [.env] file to run the Narrowspark Framework.');

            die(1);
        }

        try {
            $kernel->detectDebugMode(static function (): bool {
                $_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? null;

                /** @var null|bool $appDebug */
                $appDebug = (bool) $_SERVER['APP_DEBUG'] || \filter_var($_SERVER['APP_DEBUG'], \FILTER_VALIDATE_BOOLEAN) ? true : null;

                if ($appDebug === null) {
                    throw new RuntimeException('[APP_DEBUG] environment variable is not defined.');
                }

                return $appDebug;
            });
        } catch (RuntimeException $exception) {
            $output->writeln($exception->getMessage());
            $output->writeln('You need to define environment variables in your [.env] file to run the Narrowspark Framework.');

            die(1);
        }
    }
}
