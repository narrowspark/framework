<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Displayer;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Component\Contract\Exception\Displayer as DisplayerContract;
use Viserio\Component\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Whoops\Handler\Handler;
use Whoops\Run as Whoops;

abstract class AbstractWhoopsDisplayer implements DisplayerContract
{
    use ResponseFactoryAwareTrait;

    /**
     * Create a new whoops displayer instance.
     *
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
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

        foreach (\array_merge($headers, ['Content-Type' => $this->getContentType()]) as $header => $value) {
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
    public function canDisplay(Throwable $original, Throwable $transformed, int $code): bool
    {
        return \class_exists(Whoops::class);
    }

    /**
     * Get the Whoops handler.
     *
     * @return \Whoops\Handler\Handler
     */
    abstract protected function getHandler(): Handler;

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
        $whoops->pushHandler($this->getHandler());

        return $whoops;
    }
}
