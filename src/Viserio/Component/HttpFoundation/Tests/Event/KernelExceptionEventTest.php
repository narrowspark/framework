<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFoundation\Tests\Event;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\HttpFoundation\Event\KernelExceptionEvent;

/**
 * @internal
 */
final class KernelExceptionEventTest extends MockeryTestCase
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
     * @var \Mockery\MockInterface|\Psr\Http\Message\ResponseInterface
     */
    private $responseMock;

    /**
     * @var \Viserio\Component\HttpFoundation\Event\KernelExceptionEvent
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
        $this->responseMock      = $this->mock(ResponseInterface::class);

        $this->event = new KernelExceptionEvent($this->kernelMock, $this->serverRequestMock, $this->responseMock);
    }

    public function testGetName(): void
    {
        $this->assertSame(HttpKernelContract::EXCEPTION, $this->event->getName());
    }

    public function testGetTarget(): void
    {
        $this->assertSame($this->kernelMock, $this->event->getTarget());
    }

    public function testGetParams(): void
    {
        $this->assertSame(['server_request' => $this->serverRequestMock, 'response' => $this->responseMock], $this->event->getParams());
    }
}
