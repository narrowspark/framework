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

namespace Viserio\Component\Pipeline\Tests\Fixture;

class PipelineInvokePipe
{
    public static $run;

    protected $arg;

    public function __construct($arg = null)
    {
        $this->arg = $arg;
    }

    public function __invoke($piped, $next)
    {
        $_SERVER['__test.pipe.parameters'] = $this->arg;

        return $next($piped);
    }
}
