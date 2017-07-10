<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Console;

use Symfony\Component\Console\Input\InputInterface;

interface Terminable
{
    /**
     * Terminate the application.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param int                                             $status
     *
     * @return void
     */
    public function terminate(InputInterface $input, int $status): void;
}
