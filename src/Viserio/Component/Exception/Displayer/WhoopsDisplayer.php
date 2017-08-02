<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Displayer;

use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Component\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Component\Contracts\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

class WhoopsDisplayer implements DisplayerContract
{
    use ResponseFactoryAwareTrait;

    /**
     * Create a new whoops displayer instance.
     *
     * @param \Interop\Http\Factory\ResponseFactoryInterface       $responseFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($code);

        foreach (\array_merge($headers, ['Content-Type' => $this->contentType()]) as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }

        $body = $response->getBody();
        $body->write($this->getWhoops()->handleException($exception));
        $body->rewind();

        return $response->withBody($body);
    }

    /**
     * {@inheritdoc}
     */
    public function contentType(): string
    {
        return 'text/html';
    }

    /**
     * {@inheritdoc}
     */
    public function canDisplay(Throwable $original, Throwable $transformed, int $code): bool
    {
        return \class_exists(Whoops::class);
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose(): bool
    {
        return true;
    }

    /**
     * Returns the whoops instance.
     *
     * @return Whoops
     */
    private function getWhoops(): Whoops
    {
        $whoops = new Whoops();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler(new PrettyPageHandler());

        return $whoops;
    }
}
