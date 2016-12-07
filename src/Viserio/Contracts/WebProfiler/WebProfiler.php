<?php
declare(strict_types=1);
namespace Viserio\Contracts\WebProfiler;

use Psr\Http\Message\ServerRequestInterface;

interface WebProfiler
{
    /**
     * Collects data for the given Request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     */
    public function collect(ServerRequestInterface $serverRequest);
}
