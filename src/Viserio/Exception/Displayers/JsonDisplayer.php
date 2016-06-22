<?php
namespace Viserio\Exception\Displayers;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Exceptions\ExceptionInfo;
use Viserio\Http\Response\JsonResponse;

class JsonDisplayer implements DisplayerContract
{
    /**
     * The exception info instance.
     *
     * @var \Viserio\Exceptions\ExceptionInfo
     */
    protected $info;

    /**
     * Create a new json displayer instance.
     *
     * @param \Viserio\Exceptions\ExceptionInfo $info
     *
     * @return void
     */
    public function __construct(ExceptionInfo $info)
    {
        $this->info = $info;
    }

    /**
     * {@inheritdoc}
     */
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface
    {
        $info = $this->info->generate($exception, $id, $code);
        $error = ['id' => $id, 'status' => $info['code'], 'title' => $info['name'], 'detail' => $info['detail']];

        return new JsonResponse(['errors' => [$error]], $code, array_merge($headers, ['Content-Type' => $this->contentType()]));
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
