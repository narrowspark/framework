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
     * The method name of the compile container extend function.
     *
     * @var string
     */
    private $extendCompiledMethodName;

    /**
     * ExtendersCompiler constructor.
     *
     * @param string $extendCompiledConcreteMethodName
     * @param array  $extenders
     */
    public function __construct(string $extendCompiledConcreteMethodName, array $extenders)
    {
        $this->extendCompiledMethodName = $extendCompiledConcreteMethodName;
        $this->extenders                = $extenders;
    }

    /**
     * @param string $extendCompiledMethodName
     *
     * @return string
     */
    public static function getExtendFunction(string $extendCompiledMethodName): string
    {
        return '    private function ' . $extendCompiledMethodName . '(array $extenders, &$resolved): void ' . PHP_EOL . '    {' . PHP_EOL .
            '        foreach ($extenders as $extender) {' . PHP_EOL .
            '            $resolved = $this->extendConcrete($resolved, $extender);' . PHP_EOL .
            '        }' . PHP_EOL .
            '    }' . PHP_EOL;
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
        $code .= '        $this->' . $this->extendCompiledMethodName . '($extenders, $resolved);' . PHP_EOL . PHP_EOL;

        return $code . '        return $resolved;';
    }
}
