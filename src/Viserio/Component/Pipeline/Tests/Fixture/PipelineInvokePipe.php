<?php
declare(strict_types=1);
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
