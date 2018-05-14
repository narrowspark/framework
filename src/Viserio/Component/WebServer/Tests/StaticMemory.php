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
