<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Console;

use Symfony\Component\Console\Input\InputInterface;

interface Terminable
{
    /**
     * Terminate the application.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param int                                             $status
     */
    public function terminate(InputInterface $input, int $status);
}
