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
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\OptionsResolver\Traits\ConfigurationTrait;

class HtmlDisplayer implements DisplayerContract, RequiresComponentConfigContract, ProvidesDefaultOptionsContract
{
    use ConfigurationTrait;
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
     * @param \Interop\Container\ContainerInterface|iterable $data
     */
    public function __construct(ExceptionInfo $info, ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory, $data)
    {
        $this->info            = $info;
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;

        $this->configureOptions($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'exception'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'template_path' => __DIR__ . '/../Resources/error.html',
        ];
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
        $content = file_get_contents($this->options['template_path']);

        foreach ($info as $key => $val) {
            $content = str_replace("{{ $$key }}", $val, $content);
        }

        return $content;
    }
}
