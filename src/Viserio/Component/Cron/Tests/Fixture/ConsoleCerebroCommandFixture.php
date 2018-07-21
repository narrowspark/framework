<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Fixture;

use Viserio\Component\Console\Command\AbstractCommand;

class ConsoleCerebroCommandFixture extends AbstractCommand
{
    protected $signature = 'foo:bar';

    protected $foo;

    public function __construct(DummyClassFixture $foo)
    {
        parent::__construct();

        $this->foo = $foo;
    }
}
