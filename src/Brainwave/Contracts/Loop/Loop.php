<?php

namespace Brainwave\Contracts\Loop;

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
 * @version     0.10-dev
 */

/**
 * Loop.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10-dev
 */
interface Loop
{
    /**
     * Register a listener to be notified when a stream is ready to read.
     *
     * @param stream   $stream   The PHP stream resource to check.
     * @param callable $listener Invoked when the stream is ready.
     */
    public function onReadable($stream, callable $listener);

    /**
     * Enables readable notifications when a stream is ready to write.
     *
     * @param stream $stream The PHP stream resource to check.
     */
    public function enableRead($stream);

    /**
     * Disables readable notifications when a stream is ready to write.
     *
     * @param stream $stream The PHP stream resource to check.
     */
    public function disableRead($stream);

    /**
     * Register a listener to be notified when a stream is ready to write.
     *
     * @param stream   $stream   The PHP stream resource to check.
     * @param callable $listener Invoked when the stream is ready.
     */
    public function onWritable($stream, callable $listener);

    /**
     * Enables writable notifications when a stream is ready to write.
     *
     * @param stream $stream The PHP stream resource to check.
     */
    public function enableWrite($stream);

    /**
     * Disables writable notifications when a stream is ready to write.
     *
     * @param stream $stream The PHP stream resource to check.
     */
    public function disableWrite($stream);

    /**
     * Remove all listeners for the given stream.
     *
     * @param stream $stream The PHP stream resource.
     */
    public function remove($stream);
}
