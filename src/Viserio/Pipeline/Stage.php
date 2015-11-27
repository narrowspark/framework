<?php
namespace Viserio\Pipeline;

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

use Closure;
use Exception;
use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Viserio\Contracts\Pipeline\Stage as StageContract;

/**
 * Stage.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0
 */
class Stage implements StageContract
{
    /**
     * The next stop.
     *
     * @var null
     */
    private $stop = null;

    /**
     * The exception that was thrown while processing a handler
     *
     * @var null
     */
    private $exception = null;

   /**
     * Set the stops of the pipeline.
     *
     * @param StageContract $stops
     *
     * @return void
     */
    public function through(StageContract $stop)
    {
        $this->stop = $stop;
    }

    /**
     * Set Exception
     * An exception that halted this stage.
     *
     * @param \Exception $exception
     */
    public function setException(Exception $e)
    {
        $this->exception = $e;
    }

    /**
     * Get Exception
     * An exception that halted this stage.
     *
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Was the stage halted by a thrown Exception.
     *
     * @return bool
     */
    public function isException()
    {
        return !is_null($this->exception);
    }

    /**
     * Execute the task for this stage
     *
     * @param array $input the input param for this task
     *
     * @return boolean success status
     */
    public function handle(array $input)
    {
        try {
            $status = $this->process($input);
        } catch (Exception $e) {
            $status = false;
            $this->setException($e);
        }

        return $status;
    }

    /**
     * Execute the task for this stage
     *
     * @param array $input the input param for this task
     *
     * @return boolean success status
     */
    abstract protected function process(array $input)
    {

    }
}
