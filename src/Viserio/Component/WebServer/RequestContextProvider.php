<?php
declare(strict_types=1);
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
     *
     * @param \Psr\Http\Message\ServerRequestInterface $currentRequest
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
            'uri'        => (string) $this->currentRequest->getUri(),
            'method'     => $this->currentRequest->getMethod(),
            'identifier' => \spl_object_hash($this->currentRequest),
        ];
    }
}
