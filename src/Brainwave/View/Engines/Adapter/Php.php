<?php
namespace Brainwave\View\Engines\Adapter;

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

use Brainwave\Contracts\View\Engine as EnginesContract;

/**
 * Php.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class Php implements EnginesContract
{
    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     * @param array  $data
     *
     * @return string
     */
    public function get($path, array $data = [])
    {
        return $this->evaluatePath($path, $data);
    }

    /**
     * Get the evaluated contents of the view at the given path.
     *
     * @param string $path
     * @param array  $data
     *
     * @return string
     */
    protected function evaluatePath($path, array $data)
    {
        extract($data, EXTR_PREFIX_SAME, 'brain');

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        ob_start();

        try {
            require_once $path;
            // Return temporary output buffer content, destroy output buffer
            return ltrim(ob_get_clean());
        } catch (\Exception $exception) {
            // Return temporary output buffer content, destroy output buffer
            $this->handleViewException($exception);
        }

        return ltrim(ob_get_clean());
    }

    /**
     * Handle a view exception.
     *
     * @param \Exception $exception
     *
     * @throws $exception
     */
    protected function handleViewException($exception)
    {
        ob_get_clean();
        throw $exception;
    }
}
