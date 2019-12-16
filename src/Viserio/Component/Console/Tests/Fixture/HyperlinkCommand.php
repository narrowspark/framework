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

use Viserio\Component\Console\Command\AbstractCommand;

class HyperlinkCommand extends AbstractCommand
{
    protected $signature = 'foo:hyperlink';

    public function handle(): int
    {
        $this->hyperlink('https://narrowspark.com', 'Narrowspark');

        return 0;
    }
}
