<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Session\Tests\Handler;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Session\Handler\NullSessionHandler;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class NullSessionHandlerTest extends TestCase
{
    /** @var \Viserio\Component\Session\Handler\NullSessionHandler */
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
