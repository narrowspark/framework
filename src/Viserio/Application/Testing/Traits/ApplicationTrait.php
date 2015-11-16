<?php
namespace Viserio\Application\Testing\Traits;

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

use Viserio\Application\Application;

/**
 * TestCase.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7-dev
 */
trait ApplicationTrait
{
    /**
     * The Narrowspark application instance.
     *
     * @var \Viserio\Application\Application
     */
    protected $app;

    /**
     * Applictaion base path.
     *
     * @var array|string
     */
    protected $path = '';

    /**
     * Refresh the application instance.
     */
    protected function refreshApplication()
    {
        $this->app = $this->createApplication();

        putenv('APP_ENV=testing');
    }

    /**
     * Get base path.
     *
     * @return string
     */
    protected function getBootstrapBasePath()
    {
        if ('' === $this->path) {
            $class = new ReflectionClass($this);

            $parents = [];
            // get an array of all the parent classes
            while ($parent = $class->getParentClass()) {
                $parents[] = $parent->getName();
                $class = $parent;
            }

            // we want to select the penultimate class from the list of parents
            // this is because the class directly extending this must be the
            // abstract test case the user has used in their app
            $pos = count($parents) - 5;

            if ($pos < 0) {
                throw new \RuntimeException('The base path could not be automatically determined.');
            }

            // get the reflection class for the selected class
            $selected = new ReflectionClass($parents[$pos]);

            // get the filepath of the selected class
            $path = $selected->getFileName();

            // return the filepath one up from the folder the selected class is saved in
            return realpath(dirname($path).'/../bootstrap/paths.php');
        }

        return $this->path;
    }

    /**
     * Set base path.
     *
     * @var string|array
     */
    public function setBasePath($path)
    {
        $this->path = $path;
    }

    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Viserio\Application\Application
     */
    public function createApplication()
    {
        $app = $this->resolveApplication();
        $this->resolveApplicationCore($app);

        return $app;
    }

    /**
     * Resolve application implementation.
     *
     * @return \Viserio\Application\Application
     */
    protected function resolveApplication()
    {
        $app = new Application((array) $this->getBootstrapBasePath());

        return $app;
    }

    /**
     * Resolve application core implementation.
     *
     * @param \Viserio\Application\Application $app
     */
    protected function resolveApplicationCore($app)
    {
        $app->detectEnvironment(function () {
            return 'testing';
        });
    }
}
