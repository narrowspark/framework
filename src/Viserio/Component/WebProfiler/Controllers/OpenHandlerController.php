<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Controllers;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Component\Routing\AbstractController;
use Viserio\Component\WebProfiler\WebProfiler;

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
     * WebProfiler instance.
     *
     * @var \Viserio\Component\WebProfiler\WebProfiler
     */
    protected $webprofiler;

    /**
     * [__construct description].
     *
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     * @param \Interop\Http\Factory\StreamFactoryInterface   $streamFactory
     * @param \Viserio\Component\WebProfiler\WebProfiler               $webprofiler
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        WebProfiler $webprofiler
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->webprofiler     = $webprofiler;
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
