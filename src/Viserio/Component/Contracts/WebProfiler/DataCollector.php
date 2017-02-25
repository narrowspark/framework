<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\WebProfiler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface DataCollector
{
    /**
     * Collects data for the given Request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     * @param \Psr\Http\Message\ResponseInterface      $response
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response);

    /**
     * Returns the unique name of the collector.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns infos for a tab.
     *  - icon
     *  - label
     *  - value
     *  - class.
     *
     * @return array
     */
    public function getMenu(): array;

    /**
     * Get the Tab postion from a collector.
     * Choose between left or right postion.
     *
     * @return string
     */
    public function getMenuPosition(): string;
}
