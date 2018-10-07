<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Foundation;

interface Bootstrap
{
    /**
     * Returns the bootstrap priority.
     *
     * @return int
     */
    public static function getPriority(): int;

    /**
     * Bootstrap the given kernel.
     *
     * @param \Viserio\Component\Contract\Foundation\Kernel $app
     *
     * @return void
     */
    public static function bootstrap(Kernel $app): void;
}
