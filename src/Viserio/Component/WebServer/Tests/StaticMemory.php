<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\WebServer\Tests;

/**
 * @internal
 */
final class StaticMemory
{
    /** @var false|resource */
    public static $result;

    /** @var int */
    public static $pcntlFork;

    /** @var int */
    public static $posixSetsid;
}
