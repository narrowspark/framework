<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Fixture;

use Viserio\Component\Console\Command\AbstractCommand;

class HelloCommand extends AbstractCommand
{
    /**
     * @var null|string The default command name
     */
    protected static $defaultName = 'hello';

    public function handle(LazyWhiner $lazyWhiner): int
    {
        $lazyWhiner->whine($this);

        $this->getOutput()->write('Hello World!');

        return 0;
    }
}
