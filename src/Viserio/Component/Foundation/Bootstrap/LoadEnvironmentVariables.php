<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Symfony\Component\Console\Input\ArgvInput;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Support\Env;

class LoadEnvironmentVariables implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(KernelContract $kernel): void
    {
        if (file_exists($kernel->getStoragePath('config.cache'))) {
            return;
        }

        $this->checkForSpecificEnvironmentFile($kernel);

        try {
            (new Dotenv($kernel->getEnvironmentPath(), $kernel->getEnvironmentFile()))->load();
        } catch (InvalidPathException $exception) {
        }
    }

    /**
     * Detect if a custom environment file matching the APP_ENV exists.
     *
     * @param \Viserio\Component\Contracts\Foundation\Kernel $kernel
     *
     * @return void
     */
    protected function checkForSpecificEnvironmentFile(KernelContract $kernel): void
    {
        if ($kernel->isRunningInConsole() && ($input = new ArgvInput())->hasParameterOption('--env')) {
            $this->setEnvironmentFilePath(
                $kernel,
                $kernel->getEnvironmentFile() . '.' . $input->getParameterOption('--env')
            );
        }

        $env = Env::get('APP_ENV');

        if (! $env) {
            return;
        }

        $this->setEnvironmentFilePath(
            $kernel,
            $kernel->getEnvironmentFile() . '.' . $env
        );
    }

    /**
     * Load a custom environment file.
     *
     * @param \Viserio\Component\Contracts\Foundation\Kernel $kernel
     * @param string                                         $file
     *
     * @return void
     */
    protected function setEnvironmentFilePath(KernelContract $kernel, string $file): void
    {
        if (file_exists($kernel->getEnvironmentPath() . '/' . $file)) {
            $kernel->loadEnvironmentFrom($file);
        }
    }
}
