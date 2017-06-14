<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Controller;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Routing\AbstractController;

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
     * @var \Viserio\Component\Contracts\Profiler\Profiler
     */
    protected $profiler;

    /**
     * Create a new OpenHandler Controller instance.
     *
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     * @param \Interop\Http\Factory\StreamFactoryInterface   $streamFactory
     * @param \Viserio\Component\Contracts\Profiler\Profiler $profiler
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ProfilerContract $profiler
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->profiler        = $profiler;
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
