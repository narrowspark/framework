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
 * @version     0.10.0
 */

use Viserio\Application\EnvironmentDetector;

/**
 * EnvironmentTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
trait EnvironmentTrait
{
    /**
     * Get or check the current application environment.
     *
     * @param  mixed
     *
     * @return string
     */
    public function environment()
    {
        if (func_num_args() > 0) {
            $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

            foreach ($patterns as $pattern) {
                if (str_is($pattern, $this->get('env'))) {
                    return true;
                }
            }

            return false;
        }

        return $this->get('env');
    }

    /**
     * Determine if application is in local environment.
     *
     * @return bool
     */
    public function isLocal()
    {
        return $this->get('env') === 'local';
    }

    /**
     * Detect the application's current environment.
     *
     * @param \Closure $callback
     *
     * @return string
     */
    public function detectEnvironment(\Closure $callback)
    {
        $args = (false !== getenv('argv') ? getenv('argv') : null);

        $this->bind('env', (new EnvironmentDetector())->detect($callback, (array) $args));

        return $this->get('env');
    }

    /**
     * Determine if we are running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return $this->get('environment')->runningInConsole();
    }

    /**
     * Determine if we are running unit tests.
     *
     * @return bool
     */
    public function runningUnitTests()
    {
        return $this->get('env') === 'testing';
    }

    /**
     * {@inheritdoc}
     *
     * @param string $alias
     * @param string $concrete
     */
    abstract public function bind($alias, $concrete = null, $singleton = false);

    /**
     * {@inheritdoc}
     *
     * @param string $id
     */
    abstract public function get($id);
}
