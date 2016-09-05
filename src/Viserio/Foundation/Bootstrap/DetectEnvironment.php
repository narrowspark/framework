<?php
declare(strict_types=1);
namespace Viserio\Foundation\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Viserio\Contracts\Foundation\Application;
use Viserio\Contracts\Foundation\Bootstrap as BootstrapContract;

class DetectEnvironment implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app)
    {
        if (! $app->configurationIsCached()) {
            $this->checkForSpecificEnvironmentFile($app);

            try {
                (new Dotenv($app->environmentPath(), $app->environmentFile()))->load();
            } catch (InvalidPathException $exception) {
                //
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
        if (! getenv('APP_ENV')) {
            return;
        }

        $file = $app->environmentFile() . '.' . getenv('APP_ENV');

        if (file_exists($app->environmentPath() . '/' . $file)) {
            $app->loadEnvironmentFrom($file);
        }
    }
}
