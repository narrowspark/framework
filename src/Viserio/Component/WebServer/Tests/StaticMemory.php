<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Tests;

/**
 * @internal
 */
final class StaticMemory
{
    public static $result;

    public static $pcntlFork;

    public static $posixSetsid;
}
