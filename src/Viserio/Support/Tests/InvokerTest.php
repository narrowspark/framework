<?php
namespace Viserio\Support\Tests;

use Viserio\Support\Invoker;
use Viserio\Support\Tests\Fixture\MockContainer;

class InvokerTest extends \PHPUnit_Framework_TestCase
{
    public function testCall()
    {
        $invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer(new MockContainer());

        $call =  $invoker->call(function ($name) {
            return 'Hello ' . $name;
        }, ['John']);

        $this->assertEquals('Hello John', $call);
    }
}
