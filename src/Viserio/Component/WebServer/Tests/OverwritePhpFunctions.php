<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\WebServer;

use Viserio\Component\WebServer\Tests\StaticMemory;

/**
 * Open Internet or Unix domain socket connection.
 *
 * @see https://php.net/manual/en/function.fsockopen.php
 *
 * @param string $hostname <p>
 *                         If you have compiled in OpenSSL support, you may prefix the
 *                         hostname with either ssl://
 *                         or tls:// to use an SSL or TLS client connection
 *                         over TCP/IP to connect to the remote host.
 *                         </p>
 * @param int    $port     [optional] <p>
 *                         The port number.
 *                         </p>
 * @param int    $errno    [optional] <p>
 *                         If provided, holds the system level error number that occurred in the
 *                         system-level connect() call.
 *                         </p>
 *                         <p>
 *                         If the value returned in errno is
 *                         0 and the function returned false, it is an
 *                         indication that the error occurred before the
 *                         connect() call. This is most likely due to a
 *                         problem initializing the socket.
 *                         </p>
 * @param string $errstr   [optional] <p>
 *                         The error message as a string.
 *                         </p>
 * @param float  $timeout  [optional] <p>
 *                         The connection timeout, in seconds.
 *                         </p>
 *                         <p>
 *                         If you need to set a timeout for reading/writing data over the
 *                         socket, use stream_set_timeout, as the
 *                         timeout parameter to
 *                         fsockopen only applies while connecting the
 *                         socket.
 *                         </p>
 *
 * @return false|resource fsockopen returns a file pointer which may be used
 *                        together with the other file functions (such as
 *                        fgets, fgetss,
 *                        fwrite, fclose, and
 *                        feof). If the call fails, it will return false
 *
 * @since 4.0
 * @since 5.0
 */
function fsockopen(
    $hostname,
    $port = null,
    ?int &$errno = null,
    ?int &$errstr = null,
    $timeout = null
) {
    return StaticMemory::$result;
}

/**
 * Closes an open file pointer.
 *
 * @see https://php.net/manual/en/function.fclose.php
 *
 * @param bool|resource $handle <p>
 *                              The file pointer must be valid, and must point to a file successfully
 *                              opened by fopen or fsockopen.
 *                              </p>
 *
 * @return bool true on success or false on failure
 *
 * @since 4.0
 * @since 5.0
 */
function fclose($handle): bool
{
    if (\is_resource($handle)) {
        \fclose($handle);
    }

    return true;
}

/**
 * Forks the currently running process.
 *
 * @see https://php.net/manual/en/function.pcntl-fork.php
 *
 * @return int On success, the PID of the child process is returned in the
 *             parent's thread of execution, and a 0 is returned in the child's
 *             thread of execution. On failure, a -1 will be returned in the
 *             parent's context, no child process will be created, and a PHP
 *             error is raised.
 *
 * @since 4.1.0
 * @since 5.0
 */
function pcntl_fork(): int
{
    return StaticMemory::$pcntlFork;
}

/**
 * Make the current process a session leader.
 *
 * @see https://php.net/manual/en/function.posix-setsid.php
 *
 * @return int the session id, or -1 on errors
 *
 * @since 4.0
 * @since 5.0
 */
function posix_setsid(): int
{
    return StaticMemory::$posixSetsid;
}
