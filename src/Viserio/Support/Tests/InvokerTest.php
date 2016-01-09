<?php
namespace Viserio\Support\Tests;

use Viserio\Support\Invoker;
use Narrowspark\TestingHelper\ArrayContainer;

class InvokerTest extends \PHPUnit_Framework_TestCase
{
    public function testCall()
    {
        $invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer(new ArrayContainer());

        $call =  $invoker->call(function ($name) {
            return 'Hello ' . $name;
        }, ['John']);

        $this->assertEquals('Hello John', $call);
    }
}
