<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler;

use Closure;
use Viserio\Component\Container\Compiler\Traits\AnalyzedClosureTrait;
use Viserio\Component\Contract\Container\Types as TypesContract;

final class ExtendersCompiler extends AbstractCompiler
{
    use AnalyzedClosureTrait;

    /**
     * The container's extenders.
     *
     * @var array
     */
    private $extenders;

    /**
     * @var string
     */
    private $extendCompiledConcreteMethodName;

    /**
     * ExtendersCompiler constructor.
     *
     * @param string $extendCompiledConcreteMethodName
     * @param array  $extenders
     */
    public function __construct(string $extendCompiledConcreteMethodName, array $extenders)
    {
        $this->extendCompiledConcreteMethodName = $extendCompiledConcreteMethodName;
        $this->extenders                        = $extenders;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported(string $id, array $binding): bool
    {
        return $this->extenders[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $id, array $binding): string
    {
        $value = $binding[TypesContract::VALUE];

        $code = '        $resolved = ' . $this->compileValue($value) . ';' . PHP_EOL . PHP_EOL;

        $extenders = \array_map(function (Closure $extender) {
            return 'static ' . $this->getAnalyzedClosure($extender);
        }, $this->extenders[$id]);

        $code .= '        $extenders = [' . PHP_EOL . '        ' . \implode(',' . PHP_EOL . '        ', $extenders) . PHP_EOL . '        ];' . PHP_EOL . PHP_EOL;
        $code .= '        $this->' . $this->extendCompiledConcreteMethodName . '($extenders, $resolved);' . PHP_EOL . PHP_EOL;

        return $code . '        return $resolved;';
    }
}
