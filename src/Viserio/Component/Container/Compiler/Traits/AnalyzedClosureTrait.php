<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler\Traits;

use Closure;
use Opis\Closure\ReflectionClosure;

trait AnalyzedClosureTrait
{
    /**
     * Analyze a closure with opis.
     *
     * @param \Closure $closure
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    protected function getAnalyzedClosure(Closure $closure): string
    {
        $closureAnalyzer = new ReflectionClosure($closure);

        // Trim spaces and the last `;`
        return \trim($closureAnalyzer->getCode(), "\t\n\r;");
    }
}
