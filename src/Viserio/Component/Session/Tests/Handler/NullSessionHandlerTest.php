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
    protected function setUp()
    {
        parent::setUp();

        $this->handler = new NullSessionHandler();
    }

    public function testOpen()
    {
        self::assertTrue($this->handler->open('/', 'test'));
    }

    public function testClose()
    {
        self::assertTrue($this->handler->close());
    }

    public function testValidateId()
    {
        self::assertTrue($this->handler->validateId('test'));
    }

    public function testUpdateTimestamp()
    {
        self::assertTrue($this->handler->updateTimestamp('test', ''));
    }

    public function testGc()
    {
        self::assertTrue($this->handler->gc(100));
    }

    public function testRead()
    {
        self::assertSame('', $this->handler->read('test'));
    }

    public function testWrite()
    {
        self::assertTrue($this->handler->write('test', ''));
    }

    public function testDestroy()
    {
        self::assertTrue($this->handler->destroy('test'));
    }
}
