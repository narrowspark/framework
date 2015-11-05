<?php
namespace Brainwave\Contracts\Http;

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

/**
 * Response.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface Response
{
    /**
     * Send HTTP headers and body.
     *
     * @return \Brainwave\Http\Response
     */
    public function send();

    /**
     * Set the content on the response.
     *
     * @param mixed $content
     *
     * @return \Brainwave\Http\Response
     */
    public function setContent($content);
}
