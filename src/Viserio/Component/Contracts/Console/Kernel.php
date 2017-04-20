<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Component\Contracts\Foundation\Kernel as BaseKernel;

interface Kernel extends BaseKernel
{
    /**
     * Handle an incoming console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface        $input
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     *
     * @return int
     */
    public function handle(InputInterface $input, OutputInterface $output = null): int;

    /**
     * Get all of the commands registered with the console.
     *
     * @return array
     */
    public function getAll(): array;
}
