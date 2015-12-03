<?php
namespace Viserio\View\Engines\Adapter;

use Dwoo\Core;
use Dwoo\Template\File;
use Viserio\Contracts\View\Engine as EnginesContract;

/**
 * Dwoo.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class Dwoo implements EnginesContract
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
        if (!is_file($path)) {
            throw new \RuntimeException(
                sprintf('Cannot render template [%s] because the template does not exist.
                Make sure your view´s template directory is correct.', $path)
            );
        }

        try {
            $dwoo = new Core();

            // read template file
            $tpl = new File($path);

            // interpolate values into template
            // send interpolated result to output device
            return $dwoo->get($tpl, $data);
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
