<?php
namespace Viserio\Contracts\Exception;

use Psr\Http\Message\ResponseInterface;

interface Displayer
{
    /**
     * Display the given exception.
     *
     * @param \Exception|\Throwable $exception
     * @param string                $id
     * @param int                   $code
     * @param string[]              $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function display($exception, string $id, int $code, array $headers): ResponseInterface;

    /**
     * Get the supported content type.
     *
     * @return string
     */
    public function contentType(): string;

    /**
     * Can we display the exception?
     *
     * @param \Exception|\Throwable $original
     * @param \Exception|\Throwable $transformed
     * @param int                   $code
     *
     * @return bool
     */
    public function canDisplay($original, $transformed, int $code): bool;

    /**
     * Do we provide verbose information about the exception?
     *
     * @return bool
     */
    public function isVerbose(): bool;
}
