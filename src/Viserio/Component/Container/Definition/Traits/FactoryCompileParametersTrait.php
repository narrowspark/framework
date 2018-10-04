<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition\Traits;

use Psr\Container\ContainerInterface;
use Viserio\Component\Container\Compiler\CompileHelper;
use Viserio\Component\Contract\Container\Container as ContainerContract;

trait FactoryCompileParametersTrait
{
    /**
     * Compile factories parameters.
     *
     * @return null|string
     */
    private function compileParameters(): ?string
    {
        if (\count($this->parameters) === 0) {
            return null;
        }

        if (\count($this->parameters) === 1 && ! $this->parameters[0]->hasType()) {
            return '$this';
        }

        $preparedParameters = [];
        $isSkipped          = false;

        /** @var \ReflectionParameter|\Roave\BetterReflection\Reflection\ReflectionParameter $parameter */
        foreach ($this->parameters as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ContainerContract ||
                $type instanceof ContainerInterface ||
                (\count($this->parameters) >= 2 && ! $this->parameters[0]->hasType() && $isSkipped === false)
            ) {
                $isSkipped            = true;
                $preparedParameters[] = '$this';
            } else {
                $preparedParameters[] = CompileHelper::toVariableName($parameter->getName());
            }
        }

        $parameters = \implode(', ', $preparedParameters);

        if ($parameter === '') {
            return null;
        }

        return $parameters;
    }
}
