<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests\DataCollector;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;
use Viserio\Component\Events\DataCollector\WrappedListener;

class WrappedListenerTest extends TestCase
{
    /**
     * @dataProvider getListeners
     *
     * @param mixed $listener
     * @param mixed $pretty
     */
    public function testGetPretty($listener, $pretty)
    {
        $wrappedListener = new WrappedListener($listener, 'name', $this->createStopwatchMock());

        $this->assertSame($pretty, $wrappedListener->getPretty());
    }

    /**
     * @dataProvider getListeners
     *
     * @param mixed $listener
     * @param mixed $pretty
     */
    public function testStub($listener, $pretty)
    {
        $wrappedListener = new WrappedListener($listener, 'name', $this->createStopwatchMock());

        $info = $wrappedListener->getInfo('event');
        $this->assertSame($pretty . '()', (string) $info['stub']);
    }

    public function getListeners()
    {
        return [
            [[$this, 'getListeners'], __METHOD__],
            [function () {
            }, 'closure'],
            [/** @closure-proxy App\Foo::bar */ function () {
            }, 'App\Foo::bar'],
            ['strtolower', 'strtolower'],
            [new Listener(), Listener::class . '::__invoke'],
            [new DecoratedListener(), 'listener'],
            [new WrappedListener(new DecoratedListener(), 'name', $this->createStopwatchMock()), 'listener'],
        ];
    }

    private function createStopwatchMock()
    {
        return $this->getMockBuilder(Stopwatch::class)->getMock();
    }
}

class Listener
{
    public function __invoke()
    {
    }
}

class DecoratedListener
{
    public function __invoke()
    {
    }

    public function getWrappedListener()
    {
        return 'listener';
    }
}
