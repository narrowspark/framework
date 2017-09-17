<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Foundation;

interface Bootstrap
{
    /**
     * Bootstrap the given kernel.
     *
     * @param \Viserio\Component\Contract\Foundation\Kernel $app
     *
     * @return void
     */
    public function bootstrap(Kernel $app): void;
}
