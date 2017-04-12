<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Foundation;

interface Bootstrap
{
    /**
     * Bootstrap the given kernel.
     *
     * @param \Viserio\Component\Contracts\Foundation\Kernel $app
     *
     * @return void
     */
    public function bootstrap(Kernel $app): void;
}
