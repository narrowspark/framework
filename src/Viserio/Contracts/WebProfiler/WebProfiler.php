<?php
declare(strict_types=1);
namespace Viserio\Contracts\WebProfiler;

interface WebProfiler
{
    /**
     * Collects data for the given Request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     */
    public function collect(ServerRequestInterface $serverRequest);
}
