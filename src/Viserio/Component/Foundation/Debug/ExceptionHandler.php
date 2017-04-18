<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Debug;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Viserio\Component\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;

class ExceptionHandler implements ExceptionHandlerContract
{
    public function __construct()
    {
        # code...
    }

    /**
     * {@inheritdoc}
     */
    public function report(Throwable $exception): void
    {

    }

    /**
     * {@inheritdoc}
     */
    public function render(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {

    }

    /**
     * {@inheritdoc}
     */
    public function renderForConsole(OutputInterface $output, Throwable $exception): void
    {

    }
}
