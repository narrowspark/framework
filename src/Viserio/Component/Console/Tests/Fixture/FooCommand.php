<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Fixture;

use Viserio\Component\Console\Command\AbstractCommand;

class FooCommand extends AbstractCommand
{
    protected static $defaultName = 'foo:bar';

    protected $signature = 'foo:bar {id}';

    public function handle(): int
    {
        return 0;
    }
}
