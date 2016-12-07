<?php
declare(strict_types=1);
namespace Viserio\Exception\Displayers;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Exception\ExceptionInfo;
use Viserio\Http\Response\HtmlResponse;
use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Viserio\Contracts\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Contracts\HttpFactory\Traits\StreamFactoryAwareTrait;

class HtmlDisplayer implements DisplayerContract
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
     * The html template path.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new html displayer instance.
     *
     * @param \Viserio\Exception\ExceptionInfo               $info
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     * @param \Interop\Http\Factory\StreamFactoryInterface   $streamFactory
     * @param string                                         $path
     */
    public function __construct(
        ExceptionInfo $info,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        string $path
    ) {
        $this->info = $info;
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface
    {
        $info = $this->info->generate($id, $code);

        $response = $this->getResponseFactory()->createResponse($code);
        $stream = $this->getStreamFactory()->createStream($this->render($info));

        foreach (array_merge($headers, ['Content-Type' => $this->contentType()]) as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }

        return $response->withBody($stream);
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
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose(): bool
    {
        return false;
    }

    /**
     * Render the page with given info.
     *
     * @param array $info
     *
     * @return string
     */
    protected function render(array $info): string
    {
        $content = file_get_contents($this->path);

        foreach ($info as $key => $val) {
            $content = str_replace("{{ $$key }}", $val, $content);
        }

        return $content;
    }
}
