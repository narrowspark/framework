<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Middleware;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WebProfilerMiddleware implements ServerMiddlewareInterface
{
    /**
     * {@inhertidoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isHtmlResponse(ResponseInterface $response): bool
    {
        return $this->hasHeaderContains($response, 'Content-Type', 'text/html');
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    private function isHtmlAccepted(ServerRequestInterface $request): bool
    {
        return $this->hasHeaderContains($request, 'Accept', 'text/html');
    }

    /**
     * @param MessageInterface $message
     * @param string           $headerName
     * @param string           $value
     *
     * @return bool
     */
    private function hasHeaderContains(MessageInterface $message, string $headerName, string $value): bool
    {
        return strpos($message->getHeaderLine($headerName), $value) !== false;
    }

    /**
     * Returns a boolean TRUE for if the response has redirect status code.
     *
     * Five common HTTP status codes indicates a redirection beginning from 301.
     * 304 not modified and 305 use proxy are not redirects.
     *
     * @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection
     *
     * @param \Psr\Http\Message\ResponseInterface $message
     *
     * @return bool
     */
    private function isRedirect(ResponseInterface $response): bool
    {
        return in_array($response->getStatusCode(), [301, 302, 303, 307, 308]);
    }
}
