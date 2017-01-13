<?php
declare(strict_types=1);
namespace Viserio\Exception\Transformers;

use ErrorException;
use Exception;
use Throwable;
use Viserio\Contracts\Exception\Transformer as TransformerContract;
use Symfony\Component\Debug\FatalErrorHandler\UndefinedMethodFatalErrorHandler;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\OutOfMemoryException;

class UndefinedMethodFatalErrorTransformer implements TransformerContract
{
    /**
     * {@inheritdoc}
     */
    public function transform(Throwable $exception): Throwable
    {
        if ($exception instanceof FatalErrorException && ! $exception instanceof OutOfMemoryException) {
            $error = [
                'type'    => $exception->getSeverity(),
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ];

            $handler = new UndefinedMethodFatalErrorHandler();

            if ($e = $handler->handleError($error, $exception)) {
                return $e;
            }
        }

        return $exception;
    }
}
