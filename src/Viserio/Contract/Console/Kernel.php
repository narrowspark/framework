<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Contract\Foundation\Kernel as BaseKernel;

interface Kernel extends BaseKernel
{
    /**
     * Handle an incoming console command.
     */
    public function handle(InputInterface $input, ?OutputInterface $output = null): int;

    /**
     * Get all of the commands registered with the console.
     */
    public function getAll(): array;
}
