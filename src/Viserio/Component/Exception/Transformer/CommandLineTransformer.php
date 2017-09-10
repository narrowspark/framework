<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Transformer;

use ErrorException;
use Exception;
use Throwable;
use Viserio\Component\Contract\Exception\Transformer as TransformerContract;

class CommandLineTransformer implements TransformerContract
{
    /**
     * {@inheritdoc}
     */
    public function transform(Throwable $exception): Throwable
    {
        if (PHP_SAPI === 'cli' || PHP_SAPI == 'phpdbg') {
            if ($exception instanceof ErrorException) {
                return $this->handleErrors($exception);
            }

            return $this->formatExceptions($exception);
        }
        // @codeCoverageIgnoreStart
        return $exception;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Handle the exceptions.
     *
     * @param \ErrorException $exception
     *
     * @return \ErrorException
     */
    protected function handleErrors(ErrorException $exception): ErrorException
    {
        $errorString = "%s%s in %s on line %d\n";
        $severity    = $this->determineSeverityTextValue($exception->getSeverity());

        // Let's calculate the length of the box, and set the box border.
        $dashes   = "\n+" . \str_repeat('-', \mb_strlen($severity) + 2) . "+\n";
        $severity = $dashes . '| ' . \mb_strtoupper($severity) . ' |' . $dashes;

        // Okay, now let's prep the message components.
        $error = $exception->getMessage();
        $file  = $exception->getFile();
        $line  = $exception->getLine();
        $error = \sprintf($errorString, $severity, $error, $file, $line);

        return new ErrorException($error);
    }

    /**
     * Format the exceptions.
     *
     * @param \Throwable $exception
     *
     * @return \Throwable
     */
    protected function formatExceptions(Throwable $exception): Throwable
    {
        $errorString = "+---------------------+\n| UNHANDLED EXCEPTION |\n+---------------------+\n";
        $errorString .= "Fatal error: Uncaught exception '%s' with message '%s' in %s on line %d\n\n";
        $errorString .= "Stack Trace:\n%s\n";
        $type    = \get_class($exception);
        $message = $exception->getMessage();
        $file    = $exception->getFile();
        $line    = $exception->getLine();
        $trace   = $exception->getTraceAsString();
        $error   = \sprintf($errorString, $type, $message, $file, $line, $trace);

        return new Exception($error);
    }

    /**
     * Determine the severity text.
     *
     * @codeCoverageIgnore
     *
     * @param int $severity
     *
     * @return string
     */
    protected function determineSeverityTextValue(int $severity): string
    {
        switch ($severity) {
            case E_ERROR:
            case E_USER_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                return 'Fatal Error';
            case E_PARSE:
                return 'Parse Error';
            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                return 'Warning';
            case E_NOTICE:
            case E_USER_NOTICE:
                return 'Notice';
            case E_STRICT:
                return 'Strict Standards';
            case E_RECOVERABLE_ERROR:
                return 'Catchable Error';
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return 'Deprecated';
            default:
                return 'Unknown Error';
        }
    }
}
