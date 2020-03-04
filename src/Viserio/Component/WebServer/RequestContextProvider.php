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

namespace Viserio\Component\WebServer;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;

final class RequestContextProvider implements ContextProviderInterface
{
    /**
     * A server request implantation.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $currentRequest;

    /**
     * Created a RequestContextProvider instance.
     */
    public function __construct(ServerRequestInterface $currentRequest)
    {
        $this->currentRequest = $currentRequest;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): ?array
    {
        return [
            'uri' => (string) $this->currentRequest->getUri(),
            'method' => $this->currentRequest->getMethod(),
            'identifier' => \spl_object_hash($this->currentRequest),
        ];
    }
}
