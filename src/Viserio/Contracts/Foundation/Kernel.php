<?php
declare(strict_types=1);
namespace Viserio\Contracts\Foundation;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Kernel
{
    /**
     * The REQUEST event occurs at the very beginning of request
     * dispatching.
     *
     * This event allows you to create a response for a request before any
     * other code in the framework is executed.
     *
     * @var string
     */
    const REQUEST = 'kernel.request';

    /**
     * The EXCEPTION event occurs when an uncaught exception appears.
     *
     * This event allows you to create a response for a thrown exception or
     * to modify the thrown exception.
     *
     * @var string
     */
    const EXCEPTION = 'kernel.exception';

    /**
     * The RESPONSE event occurs once a response was created for
     * replying to a request.
     *
     * This event allows you to modify or replace the response that will be
     * replied.
     *
     * @var string
     */
    const RESPONSE = 'kernel.response';

    /**
     * The FINISH_REQUEST event occurs when a response was generated for a request.
     *
     * This event allows you to reset the global and environmental state of
     * the application, when it was changed during the request.
     *
     * @var string
     */
    const FINISH_REQUEST = 'kernel.finish_request';

    /**
     * Handle an incoming HTTP request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $serverRequest): ResponseInterface;
}
