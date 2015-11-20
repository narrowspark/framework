<?php
namespace Viserio\View\Engines\Adapter;

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

use Viserio\Contracts\View\Engine as EnginesContract;
use MtHaml\Environment;
use MtHaml\Support\Php\Executor;

/**
 * Haml.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class Haml implements EnginesContract
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
     * @return string|null
     */
    protected function evaluatePath($path, array $data)
    {
        if (!is_file($path)) {
            throw new \RuntimeException(
                sprintf('Cannot render template [%s] because the template does not exist.
                Make sure your view´s template directory is correct.', $path)
            );
        }

        try {
            $haml = new Environment('php');

            $executor = new Executor($haml, [
                'cache' => 'haml',
            ]);

            // Compiles and executes the HAML template, with variables given as second
            // argument
            $executor->display($path, $data);
        } catch (\Exception $exception) {
            // Return temporary output buffer content, destroy output buffer
            $this->handleViewException($exception);
        }
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
