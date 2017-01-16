<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Foundation;

interface Bootstrap
{
    /**
     * Bootstrap the given application.
     *
     * @param \Viserio\Component\Contracts\Foundation\Application $app
     */
    public function bootstrap(Application $app);
}
