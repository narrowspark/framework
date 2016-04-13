<?php
namespace Viserio\Pipeline\Tests\Fixture;

use Closure;

class PipelineInvokePipe
{
    public static $run;

    protected $arg;

    public function __construct($arg = null)
    {
        $this->arg = $arg;
    }

    public function __invoke($piped, Closure $next)
    {
        $run = self::$run;

        if ($this->arg) {
            $run($piped, $this->arg);
        } else {
            $run($piped);
        }

        return $next($piped);
    }
}
