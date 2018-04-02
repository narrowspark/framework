<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Test;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Discovery\Lock;

class LockTest extends TestCase
{
    /**
     * @var \Viserio\Component\Discovery\Lock
     */
    private $lock;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        \unlink(__DIR__ . '/test.lock');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->lock = new Lock(__DIR__ . '/test.lock');
    }

    public function testAdd(): void
    {
        $this->lock->add('test', ['version' => '1']);

        self::assertTrue($this->lock->has('test'));
    }

    public function testRemove(): void
    {
        $this->lock->add('testRemove', ['version' => '2']);

        $this->lock->remove('testRemove');

        self::assertFalse($this->lock->has('testRemove'));
    }

    public function testWriteAndRead(): void
    {
        $this->lock->write();

        self::assertSame([], $this->lock->read());

        $this->lock->add('tests', ['version' => '3']);
        $this->lock->write();

        self::assertSame(['tests' => ['version' => '3']], $this->lock->read());
    }
}
