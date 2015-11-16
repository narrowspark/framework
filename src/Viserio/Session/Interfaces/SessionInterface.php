<?php
namespace Viserio\Session\Interfaces;

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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface as BaseSessionInterface;

/**
 * SessionInterface.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface SessionInterface extends BaseSessionInterface
{
    /**
     * Get the session handler instance.
     *
     * @return \SessionHandlerInterface
     */
    public function getHandler();

    /**
     * Determine if the session handler needs a request.
     *
     * @return bool
     */
    public function handlerNeedsRequest();

    /**
     * Set the request on the handler instance.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequestOnHandler(Request $request);
}
