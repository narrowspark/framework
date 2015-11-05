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
use MtHaml\Environment;
use MtHaml\Support\Twig\Extension;
use MtHaml\Support\Twig\Loader;

/**
 * HamlTwig.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class HamlTwig implements EnginesContract
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
                Make sure your view´s template directory is correct.', $path);
            );
        }

        try {
            $haml = new Environment('twig', [
                'enable_escaper' => false,
            ]);

            $loader = new \Twig_Loader_Filesystem('/path/to/templates');
            $twig = new \Twig_Environment($loader, [
                'cache' => '/path/to/compilation_cache',
            ]);

            $hamlLoader = new Loader($haml, $twig->getLoader());
            $twig->setLoader($hamlLoader);

            // Register the Twig extension before executing a HAML template
            $twig->addExtension(new Extension());

            // Render templates as usual
            return $twig->render($path, $data);
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
