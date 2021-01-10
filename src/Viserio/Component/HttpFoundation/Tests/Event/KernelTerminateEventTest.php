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

namespace Viserio\Component\HttpFoundation\Tests\Event;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\HttpFoundation\Event\KernelTerminateEvent;
use Viserio\Contract\HttpFoundation\Terminable as TerminableContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class KernelTerminateEventTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Viserio\Contract\HttpFoundation\Terminable */
    private $kernelMock;

    /** @var \Mockery\MockInterface|\Psr\Http\Message\ServerRequestInterface */
    private $serverRequestMock;

    /** @var \Mockery\MockInterface|\Psr\Http\Message\ResponseInterface */
    private $responseMock;

    /** @var \Viserio\Component\HttpFoundation\Event\KernelTerminateEvent */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->kernelMock = Mockery::mock(TerminableContract::class);
        $this->serverRequestMock = Mockery::mock(ServerRequestInterface::class);
        $this->responseMock = Mockery::mock(ResponseInterface::class);

        $this->event = new KernelTerminateEvent($this->kernelMock, $this->serverRequestMock, $this->responseMock);
    }

    public function testGetName(): void
    {
        self::assertSame(TerminableContract::TERMINATE, $this->event->getName());
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
