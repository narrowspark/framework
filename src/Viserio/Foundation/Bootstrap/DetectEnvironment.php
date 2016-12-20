<?php
declare(strict_types=1);
namespace Viserio\Foundation\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Foundation\Application;
use Viserio\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Support\Env;

class DetectEnvironment implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app)
    {
        $config = $app->get(RepositoryContract::class);

        if (!file_exists($config->get('patch.cached.config'))) {
            $this->checkForSpecificEnvironmentFile($app);

            try {
                (new Dotenv($app->environmentPath(), $app->environmentFile()))->load();
            } catch (InvalidPathException $exception) {
            }
        }
    }

    /**
     * Detect if a custom environment file matching the APP_ENV exists.
     *
     * @param \Viserio\Contracts\Foundation\Application $app
     */
    protected function checkForSpecificEnvironmentFile(Application $app)
    {
        $env = Env::get('APP_ENV');

        if (!$env) {
            return;
        }

        $file = $app->environmentFile() . '.' . $env;

        if (file_exists($app->environmentPath() . '/' . $file)) {
            $app->loadEnvironmentFrom($file);
        }
    }
}
