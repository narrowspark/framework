<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Transformers;

use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\OutOfMemoryException;
use Symfony\Component\Debug\FatalErrorHandler\ClassNotFoundFatalErrorHandler;
use Throwable;
use Viserio\Component\Contracts\Exception\Transformer as TransformerContract;

class ClassNotFoundFatalErrorTransformer implements TransformerContract
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

            $handler = new ClassNotFoundFatalErrorHandler();

            if ($e = $handler->handleError($error, $exception)) {
                return $e;
            }
        }

        return $exception;
    }
}
