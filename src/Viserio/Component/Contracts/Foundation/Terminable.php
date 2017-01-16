<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Foundation;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Terminable
{
    /**
     * The TERMINATE event occurs once a response was sent.
     *
     * This event allows you to run expensive post-response jobs.
     *
     * @var string
     */
    public const TERMINATE = 'kernel.terminate';

    /**
     * Terminates a request/response cycle.
     *
     * Should be called after sending the response and before shutting down the kernel.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     * @param \Psr\Http\Message\ResponseInterface      $response
     */
    public function terminate(ServerRequestInterface $serverRequest, ResponseInterface $response);
}
