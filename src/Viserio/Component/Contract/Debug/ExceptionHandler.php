<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Debug;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Component\Contract\Exception\ConsoleOutput as ConsoleOutputContract;

interface ExceptionHandler
{
    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    public function report(Throwable $exception): void;

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function render(ServerRequestInterface $request, Throwable $exception): ResponseInterface;

    /**
     * Render an exception to the console.
     *
     * @param \Viserio\Component\Contract\Exception\ConsoleOutput $output
     * @param \Throwable                                          $exception
     *
     * @return void
     */
    public function renderForConsole(ConsoleOutputContract $output, Throwable $exception): void;
}
