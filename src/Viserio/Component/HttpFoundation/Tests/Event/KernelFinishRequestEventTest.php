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
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\HttpFoundation\Event\KernelFinishRequestEvent;
use Viserio\Contract\HttpFoundation\HttpKernel as HttpKernelContract;

/**
 * @internal
 *
 * @small
 */
final class KernelFinishRequestEventTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Viserio\Contract\HttpFoundation\HttpKernel */
    private $kernelMock;

    /** @var \Mockery\MockInterface|\Psr\Http\Message\ServerRequestInterface */
    private $serverRequestMock;

    /** @var \Viserio\Component\HttpFoundation\Event\KernelFinishRequestEvent */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->kernelMock = Mockery::mock(HttpKernelContract::class);
        $this->serverRequestMock = Mockery::mock(ServerRequestInterface::class);

        $this->event = new KernelFinishRequestEvent($this->kernelMock, $this->serverRequestMock);
    }

    public function testGetName(): void
    {
        self::assertSame(HttpKernelContract::FINISH_REQUEST, $this->event->getName());
    }

    public function testGetTarget(): void
    {
        self::assertSame($this->kernelMock, $this->event->getTarget());
    }

    public function testGetParams(): void
    {
        self::assertSame(['server_request' => $this->serverRequestMock], $this->event->getParams());
    }
}
