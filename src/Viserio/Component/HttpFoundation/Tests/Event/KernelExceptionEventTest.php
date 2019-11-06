<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\HttpFoundation\Tests\Event;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\HttpFoundation\Event\KernelExceptionEvent;
use Viserio\Contract\HttpFoundation\HttpKernel as HttpKernelContract;

/**
 * @internal
 *
 * @small
 */
final class KernelExceptionEventTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Viserio\Contract\HttpFoundation\HttpKernel */
    private $kernelMock;

    /** @var \Mockery\MockInterface|\Psr\Http\Message\ServerRequestInterface */
    private $serverRequestMock;

    /** @var \Mockery\MockInterface|\Psr\Http\Message\ResponseInterface */
    private $responseMock;

    /** @var \Viserio\Component\HttpFoundation\Event\KernelExceptionEvent */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->kernelMock = Mockery::mock(HttpKernelContract::class);
        $this->serverRequestMock = Mockery::mock(ServerRequestInterface::class);
        $this->responseMock = Mockery::mock(ResponseInterface::class);

        $this->event = new KernelExceptionEvent($this->kernelMock, $this->serverRequestMock, $this->responseMock);
    }

    public function testGetName(): void
    {
        self::assertSame(HttpKernelContract::EXCEPTION, $this->event->getName());
    }

    public function testGetTarget(): void
    {
        self::assertSame($this->kernelMock, $this->event->getTarget());
    }

    public function testGetParams(): void
    {
        self::assertSame(['server_request' => $this->serverRequestMock, 'response' => $this->responseMock], $this->event->getParams());
    }
}
