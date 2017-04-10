<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Controllers;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Component\Routing\AbstractController;
use Viserio\Component\Profiler\Profiler;

class OpenHandlerController extends AbstractController
{
    /**
     * Response factory instance.
     *
     * @var \Interop\Http\Factory\ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Stream factory instance.
     *
     * @var \Interop\Http\Factory\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * Profilerstance.
     *
     * @var \Viserio\Component\Profiler\Profiler
     */
    protected $Profiler

    /**
     * [__construct description].
     *
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     * @param \Interop\Http\Factory\StreamFactoryInterface   $streamFactory
     * @param \Viserio\Component\Profiler\Profiler     $Profiler
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ProfilerroProfiler
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->Profiler  = $ProfilerContract
    }

    /**
     * [handle description].
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(): ResponseInterface
    {
    }

    /**
     * Return Clockwork output.
     *
     * @param int $id
     *
     * @throws \DebugBar\DebugBarException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function clockwork(int $id): ResponseInterface
    {
    }
}
