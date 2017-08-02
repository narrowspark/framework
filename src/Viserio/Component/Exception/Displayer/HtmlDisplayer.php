<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Displayer;

use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Component\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Component\Contracts\Exception\ExceptionInfo as ExceptionInfoContract;
use Viserio\Component\Contracts\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class HtmlDisplayer implements DisplayerContract, RequiresComponentConfigContract, ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;
    use ResponseFactoryAwareTrait;

    /**
     * The exception info instance.
     *
     * @var \Viserio\Component\Contracts\Exception\ExceptionInfo
     */
    protected $info;

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
     * @param \Viserio\Component\Contracts\Exception\ExceptionInfo $info
     * @param \Interop\Http\Factory\ResponseFactoryInterface       $responseFactory
     * @param iterable|\Psr\Container\ContainerInterface           $data
     */
    public function __construct(
        ExceptionInfoContract $info,
        ResponseFactoryInterface $responseFactory,
        $data
    ) {
        $this->info            = $info;
        $this->responseFactory = $responseFactory;

        $this->resolvedOptions = self::resolveOptions($data);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'exception'];
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

        foreach (\array_merge($headers, ['Content-Type' => $this->contentType()]) as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }

        $body = $response->getBody();
        $body->write($this->render($this->info->generate($id, $code)));
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
