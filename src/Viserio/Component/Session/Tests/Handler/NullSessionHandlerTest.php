<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Handler;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Session\Handler\NullSessionHandler;

class NullSessionHandlerTest extends TestCase
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
        self::assertInstanceOf(\SessionHandlerInterface::class, $this->handler);
        self::assertInstanceOf(\SessionUpdateTimestampHandlerInterface::class, $this->handler);
    }

    public function testOpen(): void
    {
        self::assertTrue($this->handler->open('/', 'test'));
    }

    public function testClose(): void
    {
        self::assertTrue($this->handler->close());
    }

    public function testValidateId(): void
    {
        self::assertTrue($this->handler->validateId('test'));
    }

    public function testUpdateTimestamp(): void
    {
        self::assertTrue($this->handler->updateTimestamp('test', ''));
    }

    public function testGc(): void
    {
        self::assertTrue($this->handler->gc(100));
    }

    public function testRead(): void
    {
        self::assertSame('', $this->handler->read('test'));
    }

    public function testWrite(): void
    {
        self::assertTrue($this->handler->write('test', ''));
    }

    public function testDestroy(): void
    {
        self::assertTrue($this->handler->destroy('test'));
    }
}
