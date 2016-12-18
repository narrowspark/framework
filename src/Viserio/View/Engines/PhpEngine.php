<?php
declare(strict_types=1);
namespace Viserio\View\Engines;

use ErrorException;
use ParseError;
use Throwable;
use TypeError;
use Viserio\Contracts\View\Engine as EngineContract;

class PhpEngine implements EngineContract
{
    /**
     * {@inheritdoc}
     */
    public function get(array $fileInfo, array $data = []): string
    {
        $obLevel = ob_get_level();

        ob_start();

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // clear out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        extract($data, EXTR_PREFIX_SAME, 'narrowspark');

        try {
            require $fileInfo['path'];
        } catch (Throwable $exception) {
            $this->handleViewException(
                $this->getErrorException($exception),
                $obLevel
            );
        }

        // @codeCoverageIgnoreStart
        // Return temporary output buffer content, destroy output buffer
        return ltrim(ob_get_clean());
        // @codeCoverageIgnoreEnd
    }

    /**
     * Handle a view exception.
     *
     * @param \Throwable $exception
     * @param int        $obLevel
     *
     * @throws \Throwable
     */
    protected function handleViewException(Throwable $exception, int $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $exception;
    }

    /**
     * Get a ErrorException instance.
     *
     * @param \ParseError|\TypeError|\Throwable $exception
     *
     * @return \ErrorException
     */
    private function getErrorException($exception): ErrorException
    {
        // @codeCoverageIgnoreStart
        if ($exception instanceof ParseError) {
            $message  = 'Parse error: ' . $exception->getMessage();
            $severity = E_PARSE;
        } elseif ($exception instanceof TypeError) {
            $message  = 'Type error: ' . $exception->getMessage();
            $severity = E_RECOVERABLE_ERROR;
        } else {
            $message  = $exception->getMessage();
            $severity = E_ERROR;
        }
        // @codeCoverageIgnoreEnd

        return new ErrorException(
            $message,
            $exception->getCode(),
            $severity,
            $exception->getFile(),
            $exception->getLine()
        );
    }
}
