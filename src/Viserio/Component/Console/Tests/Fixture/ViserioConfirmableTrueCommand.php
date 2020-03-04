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

namespace Viserio\Component\Console\Tests\Fixture;

use Symfony\Component\Console\Input\InputOption;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Console\Traits\ConfirmableTrait;

class ViserioConfirmableTrueCommand extends AbstractCommand
{
    use ConfirmableTrait;

    protected static $defaultName = 'confirmable';

    protected $description = 'confirmable command';

    public function confirm(string $question, bool $default = false)
    {
        return true;
    }

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        return 0;
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run.'],
        ];
    }
}
