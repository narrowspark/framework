<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition\Traits;

use Viserio\Component\Container\Compiler\CompileHelper;

trait ClassParametersCompilerTrait
{
    /**
     * @param array $classParameters
     * @param bool  $inline
     *
     * @return array
     */
    protected function compileClassParameters(array $classParameters, $inline = false): array
    {
        /** @var \ReflectionParameter|\Roave\BetterReflection\Reflection\ReflectionParameter $parameter */
        return \array_map(function ($parameter) use ($inline) {
            /** @var null|\Roave\BetterReflection\Reflection\ReflectionClass $class */
            $class = $parameter->getClass();

            if ($inline && $class !== null && $class->isInstantiable()) {
                return \sprintf('new \\%s()', $class->getName());
            }

            if ($inline && ($parameter->isOptional() || $parameter->isDefaultValueAvailable())) {
                $defaultValue = 'null';

                if ($parameter->isDefaultValueAvailable()) {
                    $defaultValue = $parameter->getDefaultValue();

                    if ($defaultValue === null) {
                        return 'null';
                    }
                }

                return $defaultValue;
            }

            return CompileHelper::toVariableName($parameter->getName());
        }, $classParameters);
    }
}
