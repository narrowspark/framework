<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use ErrorException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Throwable;
use Viserio\Component\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Viserio\Component\Contracts\Exception\Handler as HandlerContract;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Exception\Providers\ExceptionServiceProvider;

class HandleExceptions extends AbstractLoadFiles implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(KernelContract $kernel): void
    {
        $container = $kernel->getContainer();

        if (class_exists(ExceptionServiceProvider::class)) {
            $container->register(new ExceptionServiceProvider());

            $container->get(HandlerContract::class)->register();
        } else {
            error_reporting(-1);

            set_error_handler([$this, 'handleError']);

            set_exception_handler([$this, 'handleException']);

            register_shutdown_function([$this, 'handleShutdown']);

            $container->instance(ExceptionHandlerContract::class, '');
        }
    }

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    public function handleException(Throwable $exception): void
    {
    }

    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param array  $context
     *
     * @throws \ErrorException
     *
     * @return void
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0, array $context = []): void
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle the PHP shutdown event.
     *
     * @return void
     */
    public function handleShutdown(): void
    {
        if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalExceptionFromError($error, 0));
        }
    }

    /**
     * Create a new fatal exception instance from an error array.
     *
     * @param array    $error
     * @param int|null $traceOffset#
     *
     * @return \Symfony\Component\Debug\Exception\FatalErrorException
     */
    protected function fatalExceptionFromError(array $error, int $traceOffset = null): FatalErrorException
    {
        return new FatalErrorException(
            $error['message'],
            $error['type'],
            0,
            $error['file'],
            $error['line'],
            $traceOffset
        );
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param int $type
     *
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }
}
