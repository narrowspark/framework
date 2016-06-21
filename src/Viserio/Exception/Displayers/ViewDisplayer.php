<?php
namespace Viserio\Exception\Displayers;

use Psr\Http\Message\ResponseInterface;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Exceptions\ExceptionInfo;
use Viserio\Http\Response\HtmlResponse;

class ViewDisplayer implements DisplayerContract
{
    /**
     * The exception info instance.
     *
     * @var \Viserio\Exceptions\ExceptionInfo
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
     * @param \Viserio\Exceptions\ExceptionInfo $info
     * @param \Viserio\Contracts\View\Factory   $factory
     *
     * @return void
     */
    public function __construct(ExceptionInfo $info)
    {
        $this->info = $info;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function display($exception, string $id, int $code, array $headers): ResponseInterface
    {
       $info = $this->info->generate($exception, $id, $code);

        return new HtmlResponse($this->factory->create("errors.{$code}", $info), $code, array_merge($headers, ['Content-Type' => $this->contentType()]));
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
    public function canDisplay($original, $transformed, int $code): bool
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
