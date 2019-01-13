<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Stopwatch\Stopwatch;
use Viserio\Component\Events\DataCollector\WrappedListener;

/**
 * @internal
 */
final class WrappedListenerTest extends MockeryTestCase
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

        $this->assertSame($pretty, $wrappedListener->getPretty());
    }

    /**
     * @dataProvider getListeners
     *
     * @param mixed  $listener
     * @param string $pretty
     * @param string $stub
     */
    public function testStub($listener, string $pretty, string $stub): void
    {
        $wrappedListener = new WrappedListener($listener, 'name', $this->createStopwatchMock());

        $info = $wrappedListener->getInfo('event');

        $this->assertSame($stub, (string) $info['stub']);
        $this->assertNull($info['priority']);
        $this->assertSame($pretty, $info['pretty']);
    }

    /**
     * @return array
     */
    public function getListeners(): array
    {
        return [
            [[$this, 'getListeners'], __METHOD__, __METHOD__ . '(): array'],
            [static function (): void {
            }, 'closure', 'closure(): void'],
            ['strtolower', 'strtolower', 'strtolower($str)'],
            [new Listener(), Listener::class . '::__invoke', Listener::class . '::__invoke(): void'],
            [new DecoratedListener(), DecoratedListener::class . '::__invoke', DecoratedListener::class . '::__invoke(): void'],
            [[new DecoratedListener(), 'getWrappedListener'], DecoratedListener::class . '::getWrappedListener', DecoratedListener::class . '::getWrappedListener(): string'],
        ];
    }

    /**
     * @return \Mockery\MockInterface|\Symfony\Component\Stopwatch\Stopwatch
     */
    private function createStopwatchMock()
    {
        return $this->mock(Stopwatch::class);
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

    public function getWrappedListener(): string
    {
        return 'listener';
    }
}
