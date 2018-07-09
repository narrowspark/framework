<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests\DataCollector;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;
use Viserio\Component\Events\DataCollector\WrappedListener;

/**
 * @internal
 */
final class WrappedListenerTest extends TestCase
{
    /**
     * @dataProvider getListeners
     *
     * @param mixed $listener
     * @param mixed $pretty
     */
    public function testGetPretty($listener, $pretty): void
    {
        $wrappedListener = new WrappedListener($listener, 'name', $this->createStopwatchMock());

        static::assertSame($pretty, $wrappedListener->getPretty());
    }

    /**
     * @dataProvider getListeners
     *
     * @param mixed $listener
     * @param mixed $pretty
     */
    public function testStub($listener, $pretty): void
    {
        $wrappedListener = new WrappedListener($listener, 'name', $this->createStopwatchMock());

        $info = $wrappedListener->getInfo('event');

        static::assertSame($pretty . '()', (string) $info['stub']);
        static::assertNull($info['priority']);
        static::assertSame($wrappedListener->getPretty(), $info['pretty']);
    }

    public function getListeners()
    {
        return [
            [[$this, 'getListeners'], __METHOD__],
            [function (): void {
            }, 'closure'],
            ['strtolower', 'strtolower'],
            [new Listener(), Listener::class . '::__invoke'],
            [new DecoratedListener(), DecoratedListener::class . '::__invoke'],
            [[new DecoratedListener(), 'getWrappedListener'], DecoratedListener::class . '::getWrappedListener'],
        ];
    }

    private function createStopwatchMock()
    {
        return $this->getMockBuilder(Stopwatch::class)->getMock();
    }
}

class Listener
{
    public function __invoke(): void
    {
    }
}

class DecoratedListener
{
    public function __invoke(): void
    {
    }

    public function getWrappedListener()
    {
        return 'listener';
    }
}
