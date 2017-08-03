<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Displayer;

use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Component\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Component\Contracts\Exception\ExceptionInfo as ExceptionInfoContract;
use Viserio\Component\Contracts\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Component\Contracts\View\Factory as FactoryContract;

class ViewDisplayer implements DisplayerContract
{
    use ResponseFactoryAwareTrait;

    /**
     * The exception info instance.
     *
     * @var \Viserio\Component\Contracts\Exception\ExceptionInfo
     */
    protected $info;

    /**
     * The view factory instance.
     *
     * @var \Viserio\Component\Contracts\View\Factory
     */
    protected $factory;

    /**
     * Create a new html displayer instance.
     *
     * @param \Viserio\Component\Contracts\Exception\ExceptionInfo $info
     * @param \Interop\Http\Factory\ResponseFactoryInterface       $responseFactory
     * @param \Viserio\Component\Contracts\View\Factory            $factory
     */
    public function __construct(
        ExceptionInfoContract $info,
        ResponseFactoryInterface $responseFactory,
        FactoryContract $factory
    ) {
        $this->info            = $info;
        $this->responseFactory = $responseFactory;
        $this->factory         = $factory;
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

        $view = $this->factory->create("errors.{$code}", $this->info->generate($id, $code));
        $view->with('exception', $exception);

        $body = $response->getBody();
        $body->write((string) $view);
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
        return $this->factory->exists("errors.{$code}");
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose(): bool
    {
        return false;
    }
}
