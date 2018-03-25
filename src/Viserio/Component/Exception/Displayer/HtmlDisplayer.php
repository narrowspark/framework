<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Displayer;

use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Component\Contract\Exception\Displayer as DisplayerContract;
use Viserio\Component\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class HtmlDisplayer implements DisplayerContract, RequiresComponentConfigContract, ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;
    use ResponseFactoryAwareTrait;

    /**
     * The html template path.
     *
     * @var string
     */
    protected $path;

    /**
     * Resolved options.
     *
     * @var array
     */
    protected $resolvedOptions = [];

    /**
     * Create a new html displayer instance.
     *
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     * @param iterable|\Psr\Container\ContainerInterface     $data
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        $data = []
    ) {
        $this->responseFactory = $responseFactory;
        $this->resolvedOptions = self::resolveOptions($data);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'exception', 'http', 'html'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
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
        $response = $this->responseFactory->createResponse($code);

        foreach (\array_merge($headers, ['Content-Type' => $this->getContentType()]) as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }

        $body = $response->getBody();
        $body->write($this->render(ExceptionInfo::generate($id, $code)));
        $body->rewind();

        return $response->withBody($body);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(): string
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
        $content = \file_get_contents($this->resolvedOptions['template_path']);

        foreach ($info as $key => $val) {
            $content = \str_replace("{{ $$key }}", $val, $content);
        }

        return $content;
    }
}
