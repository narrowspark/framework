<?php
namespace Viserio\View\Engines\Adapter;

use Exception;
use Throwable;
use Viserio\Contracts\View\Engine as EnginesContract;

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
     * @param string $phpPath
     * @param array  $phpData
     *
     * @return string
     */
    protected function evaluatePath($phpPath, array $phpData)
    {
        $obLevel = ob_get_level();

        ob_start();

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        extract($phpData, EXTR_PREFIX_SAME, 'narrowspark');

        try {
            require_once $phpPath;
            // Return temporary output buffer content, destroy output buffer
            return ltrim(ob_get_clean());
        } catch (Exception $exception) {
            // Return temporary output buffer content, destroy output buffer
            $this->handleViewException($exception, $obLevel);
        } catch (Throwable $exception) {
            $this->handleViewException(new FatalThrowableError($exception), $obLevel);
        }

        return ltrim(ob_get_clean());
    }

    /**
     * Handle a view exception.
     *
     * @param \Exception $exception
     * @param int        $obLevel
     *
     * @throws $exception
     */
    protected function handleViewException(Exception $exception, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $exception;
    }
}
