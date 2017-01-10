<?php
declare(strict_types=1);
namespace Viserio\Exception;

use Interop\Container\ContainerInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Narrowspark\HttpStatus\Exception\AbstractClientErrorException;
use Narrowspark\HttpStatus\Exception\AbstractServerErrorException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\Exception\FlattenException;
use Throwable;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Config\Traits\ConfigAwareTrait;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Contracts\Exception\Filter as FilterContract;
use Viserio\Contracts\Exception\Handler as HandlerContract;
use Viserio\Contracts\Log\Traits\LoggerAwareTrait;
use Viserio\Exception\Displayers\HtmlDisplayer;
use Viserio\Exception\Filters\CanDisplayFilter;
use Viserio\Exception\Filters\VerboseFilter;

class Handler extends ErrorHandler implements HandlerContract
{
    use ConfigAwareTrait;
    use LoggerAwareTrait;

    /**
     * Exception displayers.
     *
     * @var array
     */
    protected $displayers = [];

    /**
     * Exception levels.
     *
     * @var array
     */
    protected $defaultLevels = [
        FatalThrowableError::class          => 'critical',
        FatalErrorException::class          => 'error',
        Throwable::class                    => 'error',
        NotFoundException::class            => 'notice',
        AbstractClientErrorException::class => 'notice',
        AbstractServerErrorException::class => 'error',
    ];

    /**
     * Exception filters.
     *
     * @var array
     */
    protected $filters = [
        VerboseFilter::class,
        CanDisplayFilter::class,
    ];

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * ExceptionIdentifier instance.
     *
     * @var \Viserio\Exception\ExceptionIdentifier
     */
    protected $exceptionIdentifier;

    /**
     * Create a new exception handler instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container           = $container;
        $this->exceptionIdentifier = new ExceptionIdentifier();

        if ($this->container->has(LoggerInterface::class)) {
            $this->logger = $this->container->get(LoggerInterface::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addDisplayer(DisplayerContract $displayer): HandlerContract
    {
        if (in_array($displayer, $this->displayers)) {
            $pos = array_search($displayer, $this->displayers);

            unset($this->displayers[$pos]);
        }

        $this->displayers[] = $displayer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayers(): array
    {
        return $this->displayers;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(FilterContract $filter): HandlerContract
    {
        $filterClass = is_object($filter) ? get_class($filter) : $filter;

        if (in_array($filterClass, $this->filters)) {
            $pos = array_search($filterClass, $this->filters);

            unset($this->filters[$pos]);
        }

        $this->filters[] = $filter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * {@inheritdoc}
     */
    public function addShouldntReport(Throwable $exception): HandlerContract
    {
        $this->dontReport[] = $exception;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function report(Throwable $exception)
    {
        if ($this->shouldntReport($exception)) {
            return;
        }

        $level = $this->getLevel($exception);
        $id    = $this->exceptionIdentifier->identify($exception);

        if ($this->logger !== null) {
            $this->getLogger()->{$level}($exception, ['identification' => ['id' => $id]]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        error_reporting(E_ALL);

        $this->registerErrorHandler();

        // The DebugClassLoader attempts to throw more helpful exceptions
        // when a class isn't found by the registered autoloaders.
        DebugClassLoader::enable();

        $this->registerExceptionHandler();

        if ($this->getContainer()->get(RepositoryContract::class)->get('exception.env', null) !== 'testing') {
            $this->registerShutdownHandler();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unregister()
    {
        restore_error_handler();
    }

    /**
     * {@inheritdoc}
     */
    public function render(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        $transformed = $this->getTransformed($exception);

        return $this->getPreparedResponse(
            $this->getContainer(),
            $exception,
            $transformed
        );
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param \Throwable $exception
     *
     * @return bool
     */
    protected function shouldntReport(Throwable $exception): bool
    {
        $dontReport = array_merge(
            $this->dontReport,
            $this->getContainer()->get(RepositoryContract::class)->get('shouldnt_report', [])
        );

        foreach ($dontReport as $type) {
            if ($exception instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the exception level.
     *
     * @param \Throwable $exception
     *
     * @return string
     */
    protected function getLevel(Throwable $exception): string
    {
        $levels = array_merge(
            $this->defaultLevels,
            $this->getContainer()->get(RepositoryContract::class)->get('exception.levels', [])
        );

        foreach ($levels as $class => $level) {
            if ($exception instanceof $class) {
                return $level;
            }
        }

        return 'error';
    }

    /**
     * Create a response for the given exception.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable|\Exception                    $exception
     * @param \Throwable|\Exception                    $transformed
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponse(
        ServerRequestInterface $request,
        $exception,
        $transformed
    ): ResponseInterface {
        $id          = $this->exceptionIdentifier->identify($exception);
        $transformed = $this->prepareAndWrapException($transformed);
        $flattened   = FlattenException::create($transformed);
        $code        = $flattened->getStatusCode();
        $headers     = $flattened->getHeaders();

        return $this->getDisplayer(
            $request,
            $exception,
            $transformed,
            $code
        )->display($transformed, $id, $code, $headers);
    }

    /**
     * Get the displayer instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $original
     * @param \Throwable                               $transformed
     * @param int                                      $code
     *
     * @return \Viserio\Contracts\Exception\Displayer
     */
    protected function getDisplayer(
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): DisplayerContract {
        $config = $this->getContainer()->get(RepositoryContract::class);

        $displayers = array_merge(
            $this->displayers,
            $config->get('exception.displayers', [])
        );

        if ($filtered = $this->getFiltered($displayers, $request, $original, $transformed, $code)) {
            return $filtered[0];
        }

        return $this->getContainer()->get($config->get('exception.default', HtmlDisplayer::class));
    }

    /**
     * Get the filtered list of displayers.
     *
     * @param \Viserio\Contracts\Exception\Displayer[] $displayers
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $original
     * @param \Throwable                               $transformed
     * @param int                                      $code
     *
     * @return \Viserio\Contracts\Exception\Displayer[]
     */
    protected function getFiltered(
        array $displayers,
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        $container = $this->getContainer();
        $filters   = array_merge(
            $this->filters,
            $container->get(RepositoryContract::class)->get('exception.filters', [])
        );

        foreach ($filters as $filter) {
            $filterClass = is_object($filter) ? $filter : $container->get($filter);

            if (! $filterClass) {
                continue;
            }

            $displayers  = $filterClass->filter($displayers, $request, $original, $transformed, $code);
        }

        return array_values($displayers);
    }

    /**
     * Get a prepared response with the transformed exception.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \Throwable|\Exception                 $exception
     * @param \Throwable|\Exception                 $transformed
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getPreparedResponse(
        ContainerInterface $container,
        $exception,
        $transformed
    ): ResponseInterface {
        try {
            $response = $this->getResponse(
                $container->get(ServerRequestInterface::class),
                $exception,
                $transformed
            );
        } catch (Throwable | Exception $exception) {
            $this->report($exception);

            $response = $container->get(ResponseFactoryInterface::class)->createResponse();
            $response = $response->withStatus(500, HttpStatus::getReasonPhrase(500));
        }

        return $response;
    }
}
