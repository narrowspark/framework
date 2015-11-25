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

use Exception

/**
 * StageException.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0
 */
interface StageException
{
    /**
     * Set Exception
     * An exception that halted this stage.
     *
     * @param \Exception $exception
     */
    public function setException(Exception $exception);

    /**
     * Get Exception
     * An exception that halted this stage.
     *
     * @return \Exception
     */
    public function getException();

    /**
     * Was the stage halted by a thrown Exception.
     *
     * @return bool
     */
    public function isException();
}
