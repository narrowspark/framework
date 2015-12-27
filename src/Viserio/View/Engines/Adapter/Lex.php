<?php
namespace Viserio\View\Engines\Adapter;

use Lex\Parser;
use Viserio\Contracts\View\Engine as EnginesContract;

class Lex implements EnginesContract
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
            $parser = new Parser();

            $template = $parser->parse(file_get_contents($path), $data);

            return $template;
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
