<?php

namespace Brainwave\Application\Traits;

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
 * @version     0.9.8-dev
 */

use Brainwave\Application\HttpKernel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * HttpHandlingTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
trait HttpHandlingTrait
{
    /**
     * @var \Brainwave\Application\HttpKernel
     */
    protected $kernel;

    /**
     * Creates a streaming response.
     *
     * @param mixed $callback A valid PHP callback
     * @param int   $status   The response status code
     * @param array $headers  An array of response headers
     *
     * @return StreamedResponse
     */
    public function stream($callback = null, $status = 200, array $headers = [])
    {
        return new StreamedResponse($callback, $status, $headers);
    }

    /**
     * Convert some data into a JSON response.
     *
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @return JsonResponse
     */
    public function json($data = [], $status = 200, array $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Sends a file.
     *
     * @param \SplFileInfo|string $file               The file to stream
     * @param int                 $status             The response status code
     * @param array               $headers            An array of response headers
     * @param null|string         $contentDisposition The type of Content-Disposition to set
     *                                                automatically with the filename
     *
     * @throws \RuntimeException When the feature is not supported, before http-foundation v2.2
     *
     * @return BinaryFileResponse
     */
    public function sendFile($file, $status = 200, array $headers = [], $contentDisposition = null)
    {
        return new BinaryFileResponse($file, $status, $headers, true, $contentDisposition);
    }

    /**
     * Register a maintenance mode event listener.
     *
     * @param \Closure $callback
     */
    public function down(\Closure $callback)
    {
        $this->get('events')->addListenerService('brainwave.app.down', $callback);
    }

    /**
     * Get the application's request stack.
     *
     * @return \Symfony\Component\HttpFoundation\RequestStack
     */
    public function getRequestStack()
    {
        return $this->get('stack.requests');
    }

    /**
     * ResolveKernel.
     *
     * @return \Brainwave\Application\HttpKernel
     */
    protected function resolveKernel()
    {
        if ($this->kernel !== null) {
            return $this->kernel;
        }

        $kernel = new HttpKernel($this->get('app'), $this->get('stack.request'));

        $this->kernel = $this->resolveStack()->resolve($kernel);

        return $this->kernel;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function resolveStack();

    /**
     * {@inheritdoc}
     *
     * @param string $id
     */
    abstract public function get($id);
}
