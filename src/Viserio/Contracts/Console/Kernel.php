<?php
declare(strict_types=1);
namespace Viserio\Contracts\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface Kernel
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
    public function all(): array;
}
