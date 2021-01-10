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
