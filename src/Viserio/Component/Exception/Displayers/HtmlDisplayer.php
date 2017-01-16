<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Displayers;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Component\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Component\Contracts\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Component\Contracts\HttpFactory\Traits\StreamFactoryAwareTrait;
use Viserio\Component\Exception\ExceptionInfo;

class HtmlDisplayer implements DisplayerContract
{
    use ResponseFactoryAwareTrait;
    use StreamFactoryAwareTrait;

    /**
     * The exception info instance.
     *
     * @var \Viserio\Component\Exception\ExceptionInfo
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
     * @param \Viserio\Component\Exception\ExceptionInfo     $info
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
        $this->info            = $info;
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->path            = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface
    {
        $info = $this->info->generate($id, $code);

        $response = $this->getResponseFactory()->createResponse($code);
        $stream   = $this->getStreamFactory()->createStream($this->render($info));

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
