<?php
declare(strict_types=1);
namespace Viserio\Exception\Displayers;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Contracts\View\Factory as FactoryContract;
use Viserio\Exception\ExceptionInfo;
use Viserio\Http\Response\HtmlResponse;

class ViewDisplayer implements DisplayerContract
{
    /**
     * The exception info instance.
     *
     * @var \Viserio\Exception\ExceptionInfo
     */
    protected $info;

    /**
     * The view factory instance.
     *
     * @var \Viserio\Contracts\View\Factory
     */
    protected $factory;

    /**
     * Create a new json displayer instance.
     *
     * @param \Viserio\Exception\ExceptionInfo $info
     * @param \Viserio\Contracts\View\Factory  $factory
     */
    public function __construct(ExceptionInfo $info, FactoryContract $factory)
    {
        $this->info = $info;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface
    {
        $info = $this->info->generate($id, $code);
        $view = $this->factory->create("errors.{$code}", $info);

        return new HtmlResponse(
            (string) $view,
            $code,
            array_merge($headers, ['Content-Type' => $this->contentType()])
        );
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
