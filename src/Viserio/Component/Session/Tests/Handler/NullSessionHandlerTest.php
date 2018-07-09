<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Handler;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Session\Handler\NullSessionHandler;

/**
 * @internal
 */
final class NullSessionHandlerTest extends TestCase
{
    /**
     * @var \Viserio\Component\Session\Handler\NullSessionHandler
     */
    private $handler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new NullSessionHandler();
    }

    public function testInstanceOf(): void
    {
        static::assertInstanceOf(\SessionHandlerInterface::class, $this->handler);
        static::assertInstanceOf(\SessionUpdateTimestampHandlerInterface::class, $this->handler);
    }

    public function testOpen(): void
    {
        static::assertTrue($this->handler->open('/', 'test'));
    }

    public function testClose(): void
    {
        static::assertTrue($this->handler->close());
    }

    public function testValidateId(): void
    {
        static::assertTrue($this->handler->validateId('test'));
    }

    public function testUpdateTimestamp(): void
    {
        static::assertTrue($this->handler->updateTimestamp('test', ''));
    }

    public function testGc(): void
    {
        static::assertTrue($this->handler->gc(100));
    }

    public function testRead(): void
    {
        static::assertSame('', $this->handler->read('test'));
    }

    public function testWrite(): void
    {
        static::assertTrue($this->handler->write('test', ''));
    }

    public function testDestroy(): void
    {
        static::assertTrue($this->handler->destroy('test'));
    }
}
