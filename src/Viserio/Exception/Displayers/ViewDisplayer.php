<?php
namespace Viserio\Exception\Displayers;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Contracts\{
    Exception\Displayer as DisplayerContract,
    View\Factory as FactoryContract
};
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
     *
     * @return void
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
        $info = $this->info->generate($exception, $id, $code);

        return new HtmlResponse(
            $this->factory->create("errors.{$code}", $info),
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
