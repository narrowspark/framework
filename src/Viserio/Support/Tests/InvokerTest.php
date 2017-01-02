<?php
declare(strict_types=1);
namespace Viserio\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Viserio\Support\Invoker;
use PHPUnit\Framework\TestCase;

class InvokerTest extends TestCase
{
    public function testCall()
    {
        $invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer(new ArrayContainer());

        $call = $invoker->call(function ($name) {
            return 'Hello ' . $name;
        }, ['John']);

        self::assertEquals('Hello John', $call);
    }
}
