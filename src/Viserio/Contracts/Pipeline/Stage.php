<?php
namespace Viserio\Contracts\Pipeline;

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

/**
 * Stage.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0
 */
interface Stage extends StageException
{
    /**
     * Execute the task for this handler
     *
     * @param array $input the input param for this task
     *
     * @return boolean success status
     */
    public function handle(array $input);

    /**
     * Set the stops of the pipeline.
     *
     * @param Stage $stop
     *
     * @return void
     */
    public function through(Stage $stop);
}
