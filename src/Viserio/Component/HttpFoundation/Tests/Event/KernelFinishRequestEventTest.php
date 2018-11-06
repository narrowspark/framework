<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFoundation\Tests\Event;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\HttpFoundation\Event\KernelFinishRequestEvent;

/**
 * @internal
 */
final class KernelFinishRequestEventTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|\Viserio\Component\Contract\Foundation\HttpKernel
     */
    private $kernelMock;

    /**
     * @var \Mockery\MockInterface|\Psr\Http\Message\ServerRequestInterface
     */
    private $serverRequestMock;

    /**
     * @var \Viserio\Component\HttpFoundation\Event\KernelFinishRequestEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->kernelMock        = $this->mock(HttpKernelContract::class);
        $this->serverRequestMock = $this->mock(ServerRequestInterface::class);

        $this->event = new KernelFinishRequestEvent($this->kernelMock, $this->serverRequestMock);
    }

    public function testGetName(): void
    {
        $this->assertSame(HttpKernelContract::FINISH_REQUEST, $this->event->getName());
    }

    public function testGetTarget(): void
    {
        $this->assertSame($this->kernelMock, $this->event->getTarget());
    }

    public function testGetParams(): void
    {
        $this->assertSame(['server_request' => $this->serverRequestMock], $this->event->getParams());
    }
}
