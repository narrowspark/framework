<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\Invoker;

/**
 * @internal
 */
final class InvokerTest extends TestCase
{
    public function testCall(): void
    {
        $invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer(new ArrayContainer([]));

        $call = $invoker->call(static function ($name) {
            return 'Hello ' . $name;
        }, ['John']);

        $this->assertEquals('Hello John', $call);
    }
}
