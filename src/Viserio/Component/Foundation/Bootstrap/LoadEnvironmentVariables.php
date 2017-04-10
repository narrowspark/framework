<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Symfony\Component\Console\Input\ArgvInput;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Support\Env;

class LoadEnvironmentVariables implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(ApplicationContract $app): void
    {
        if (file_exists($app->get(RepositoryContract::class)->get('patch.cached.config'))) {
            return;
        }

        $this->checkForSpecificEnvironmentFile($app);

        try {
            (new Dotenv($app->environmentPath(), $app->environmentFile()))->load();
        } catch (InvalidPathException $exception) {
        }
    }

    /**
     * Detect if a custom environment file matching the APP_ENV exists.
     *
     * @param \Viserio\Component\Contracts\Foundation\Application $app
     *
     * @return void
     */
    protected function checkForSpecificEnvironmentFile(ApplicationContract $app): void
    {
        $input = new ArgvInput();

        if (php_sapi_name() == 'cli' && $input->hasParameterOption('--env')) {
            $this->setEnvironmentFilePath(
                $app,
                $app->environmentFile() . '.' . $input->getParameterOption('--env')
            );
        }

        $env = Env::get('APP_ENV');

        if (! $env) {
            return;
        }

        $this->setEnvironmentFilePath(
            $app,
            $app->environmentFile() . '.' . $env
        );
    }

    /**
     * Load a custom environment file.
     *
     * @param \Viserio\Component\Contracts\Foundation\Application $app
     * @param string                                              $file
     *
     * @return void
     */
    protected function setEnvironmentFilePath(ApplicationContract $app, string $file): void
    {
        if (file_exists($app->environmentPath() . '/' . $file)) {
            $app->loadEnvironmentFrom($file);
        }
    }
}
