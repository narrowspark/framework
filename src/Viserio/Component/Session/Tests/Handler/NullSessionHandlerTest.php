<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Handler;

use PHPUnit\Framework\TestSuite;
use Viserio\Component\Session\Handler\NullSessionHandler;

class NullSessionHandlerTest extends TestSuite
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

    public function testClose()
    {
        self::assertTrue($this->handler->close());
    }
}
