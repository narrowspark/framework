<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer;

use Viserio\Component\WebServer\Tests\StaticMemory;

function fsockopen(
    $hostname,
    $port    = null,
    &$errno  = null,
    &$errstr = null,
    $timeout = null
) {
    return StaticMemory::$result;
}

function fclose($handler)
{
    if (\is_resource($handler)) {
        \fclose($handler);
    }

    return true;
}

function pcntl_fork()
{
    return StaticMemory::$pcntlFork;
}

function posix_setsid()
{
    return StaticMemory::$posixSetsid;
}
