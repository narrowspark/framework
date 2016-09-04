<?php
declare(strict_types=1);
namespace Viserio\Contracts\Foundation;

interface Bootstrap
{
    /**
     * Bootstrap the given application.
     *
     * @param \Viserio\Contracts\Foundation\Application $app
     */
    public function bootstrap(Application $app);
}
