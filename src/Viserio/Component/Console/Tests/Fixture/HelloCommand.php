<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Fixture;

use Viserio\Component\Console\Command\Command;

class HelloCommand extends Command
{
    /**
     * @var string|null The default command name
     */
    protected static $defaultName = 'hello';

    public function handle(LazyWhiner $lazyWhiner)
    {
        $lazyWhiner->whine($this);

        $this->getOutput()->write('Hello World!');
    }
}
