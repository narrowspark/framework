<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
