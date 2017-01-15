<?php
declare(strict_types=1);
namespace Viserio\Exception\Displayers;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Contracts\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Contracts\HttpFactory\Traits\StreamFactoryAwareTrait;
use Viserio\Exception\ExceptionInfo;
use Viserio\Http\Response\JsonResponse;

class JsonDisplayer implements DisplayerContract
{
    use ResponseFactoryAwareTrait;
    use StreamFactoryAwareTrait;

    /**
     * The exception info instance.
     *
     * @var \Viserio\Exception\ExceptionInfo
     */
    protected $info;

    /**
     * Create a new html displayer instance.
     *
     * @param \Viserio\Exception\ExceptionInfo               $info
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     * @param \Interop\Http\Factory\StreamFactoryInterface   $streamFactory
     */
    public function __construct(
        ExceptionInfo $info,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->info            = $info;
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface
    {
        $info  = $this->info->generate($id, $code);
        $error = ['id' => $id, 'status' => $info['code'], 'title' => $info['name'], 'detail' => $info['detail']];

        return new JsonResponse(
            ['errors' => [$error]],
            $code,
            array_merge($headers, ['Content-Type' => $this->contentType()])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function contentType(): string
    {
        return 'application/json';
    }

    /**
     * {@inheritdoc}
     */
    public function canDisplay(Throwable $original, Throwable $transformed, int $code): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose(): bool
    {
        return false;
    }
}
