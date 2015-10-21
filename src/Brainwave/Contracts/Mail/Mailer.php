<?php

namespace Brainwave\Contracts\Mail;

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
 * Mailer.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
interface Mailer
{
    /**
     * Send a new message when only a raw text part.
     *
     * @param string          $text
     * @param \Closure|string $callback
     *
     * @return int
     */
    public function raw($text, $callback);

    /**
     * Send a new message using a view.
     *
     * @param string|array    $view
     * @param array           $data
     * @param \Closure|string $callback
     *
     * @return int
     */
    public function send($view, array $data, $callback);

    /**
     * Get the array of failed recipients.
     *
     * @return array
     */
    public function failures();
}
