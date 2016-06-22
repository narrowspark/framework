<?php
namespace Viserio\Exception\Displayers;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Exception\ExceptionInfo;
use Viserio\Http\Response\HtmlResponse;

class HtmlDisplayer implements DisplayerContract
{
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
     * @param \Viserio\Exception\ExceptionInfo $info
     * @param string                            $path
     *
     * @return void
     */
    public function __construct(ExceptionInfo $info, string $path)
    {
        $this->info = $info;
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface
    {
        $info = $this->info->generate($exception, $id, $code);

        return new HtmlResponse($this->render($info), $code, array_merge($headers, ['Content-Type' => $this->contentType()]));
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
