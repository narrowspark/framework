<?php
namespace Brainwave\Exception\Adapter;

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

use Brainwave\Contracts\Exception\Adapter;
use Brainwave\Exception\Traits\ErrorHandlingTrait;

/**
 * ArrayDisplayer.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class ArrayDisplayer implements Adapter
{
    use ErrorHandlingTrait;

    /**
     * Display the given exception to the user.
     *
     * @param \Exception $exception
     * @param int        $code
     *
     * @return array
     */
    public function display(\Exception $exception, $code)
    {
        $message = $this->message($code, $exception->getMessage());

        return ['success' => false, 'code' => $message['code'], 'msg' => $message['extra']];
    }
}
