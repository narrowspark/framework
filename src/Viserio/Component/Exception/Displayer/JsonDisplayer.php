<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Displayer;

use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Component\Contract\Exception\Displayer as DisplayerContract;
use Viserio\Component\Contract\Exception\ExceptionInfo as ExceptionInfoContract;
use Viserio\Component\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;

class JsonDisplayer implements DisplayerContract
{
    use ResponseFactoryAwareTrait;

    /**
     * The exception info instance.
     *
     * @var \Viserio\Component\Contract\Exception\ExceptionInfo
     */
    protected $info;

    /**
     * Create a new html displayer instance.
     *
     * @param \Viserio\Component\Contract\Exception\ExceptionInfo $info
     * @param \Interop\Http\Factory\ResponseFactoryInterface       $responseFactory
     */
    public function __construct(
        ExceptionInfoContract $info,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->info            = $info;
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface
    {
        $info  = $this->info->generate($id, $code);
        $error = ['id' => $id, 'status' => $info['code'], 'title' => $info['name'], 'detail' => $info['detail']];

        $response = $this->responseFactory->createResponse($code);

        foreach (\array_merge($headers, ['Content-Type' => $this->contentType()]) as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }

        $body = $response->getBody();
        $body->write(\json_encode(['errors' => [$error]], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES));
        $body->rewind();

        return $response->withBody($body);
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
