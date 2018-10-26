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

    public function testOpen(): void
    {
        $this->assertTrue($this->handler->open('/', 'test'));
    }

    public function testClose(): void
    {
        $this->assertTrue($this->handler->close());
    }

    public function testValidateId(): void
    {
        $this->assertTrue($this->handler->validateId('test'));
    }

    public function testUpdateTimestamp(): void
    {
        $this->assertTrue($this->handler->updateTimestamp('test', ''));
    }

    public function testGc(): void
    {
        $this->assertTrue($this->handler->gc(100));
    }

    public function testRead(): void
    {
        $this->assertSame('', $this->handler->read('test'));
    }

    public function testWrite(): void
    {
        $this->assertTrue($this->handler->write('test', ''));
    }

    public function testDestroy(): void
    {
        $this->assertTrue($this->handler->destroy('test'));
    }
}
