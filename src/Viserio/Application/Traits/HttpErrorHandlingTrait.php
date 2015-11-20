<?php
namespace Viserio\Application\Traits;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\LengthRequiredHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionRequiredHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Viserio\Http\Response;

/**
 * HttpErrorHandlingTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
trait HttpErrorHandlingTrait
{
    /**
     * Register an application error handler.
     *
     * @param \Closure $callback
     */
    public function error(\Closure $callback)
    {
        $this->get('exception')->error($callback);
    }

    /**
     * Register an error handler for fatal errors.
     *
     * @param \Closure $callback
     */
    public function fatal(\Closure $callback)
    {
        $this->error(function (FatalErrorException $exception) use ($callback) {
            return call_user_func($callback, $exception);
        });
    }

    /**
     * Throw an HttpException with the given data.
     *
     * @param int    $code
     * @param string $message
     * @param array  $headers
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\GoneHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\ConflictHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\LengthRequiredHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\PreconditionRequiredHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException
     */
    public function abort($code, $message = '', array $headers = [])
    {
        switch ($code) {
            // error code 400
            case Response::HTTP_BAD_REQUEST:
                throw new BadRequestHttpException($message);
            // error code 401
            case Response::HTTP_UNAUTHORIZED:
                throw new UnauthorizedHttpException($message);
            // error code 403
            case Response::HTTP_FORBIDDEN:
                throw new AccessDeniedHttpException($message);
            // error code 404
            case Response::HTTP_NOT_FOUND:
                throw new NotFoundHttpException($message);
            // error code 405
            case Response::HTTP_METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedHttpException((array) $message);
            // error code 406
            case Response::HTTP_NOT_ACCEPTABLE:
                throw new NotAcceptableHttpException($message);
            // error code 409
            case Response::HTTP_CONFLICT:
                throw new ConflictHttpException($message);
            // error code 410
            case Response::HTTP_GONE:
                throw new GoneHttpException($message);
            // error code 411
            case Response::HTTP_LENGTH_REQUIRED:
                throw new LengthRequiredHttpException($message);
            // error code 412
            case Response::HTTP_PRECONDITION_FAILED:
                throw new PreconditionFailedHttpException($message);
            // error code 415
            case Response::HTTP_UNSUPPORTED_MEDIA_TYPE:
                throw new UnsupportedMediaTypeHttpException($message);
            // error code 428
            case Response::HTTP_PRECONDITION_REQUIRED:
                throw new PreconditionRequiredHttpException($message);
            // error code 429
            case Response::HTTP_TOO_MANY_REQUESTS:
                throw new TooManyRequestsHttpException($message);
            // error code 503
            case Response::HTTP_SERVICE_UNAVAILABLE:
                throw new ServiceUnavailableHttpException($message);
            // all other error codes including 500
            default:
                throw new HttpException($code, $message, null, $headers);
        }
    }

    /**
     * Register a 404 error handler.
     *
     * @param \Closure $callback
     */
    public function missing(\Closure $callback)
    {
        $this->error(function (NotFoundHttpException $exception) use ($callback) {
            return call_user_func($callback, $exception);
        });
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return file_exists($this->storagePath().'/framework/down');
    }

    /**
     * Get the path to the storage directory.
     *
     * @return string
     */
    abstract public function storagePath();

    /**
     * {@inheritdoc}
     *
     * @param string $id
     */
    abstract public function get($id);
}
