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

namespace Viserio\Provider\Framework\Bootstrap;

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
                $appEnv = $_SERVER['APP_ENV'] ?? null;

                if ($appEnv === null) {
                    throw new RuntimeException('[APP_ENV] environment variable is not defined.');
                }

                return $appEnv;
            });
            $kernel->detectDebugMode(static function (): bool {
                /** @var null|bool $appDebug */
                $appDebug = $_SERVER['APP_DEBUG'] ?? null;

                if ($appDebug === null) {
                    throw new RuntimeException('[APP_DEBUG] environment variable is not defined.');
                }

                return (bool) $appDebug;
            });
        } catch (RuntimeException $exception) {
            $output->writeln($exception->getMessage());
            $output->writeln('You need to define environment variables in your [.env] file to run the Narrowspark Framework.');

            die(1);
        }
    }
}
