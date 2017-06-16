<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Command;

use Invoker\ParameterResolver\ParameterResolver;
use ReflectionFunctionAbstract;

/**
 * Code in this class it taken from silly.
 *
 * See the original here: https://github.com/mnapoli/silly/blob/master/src/HyphenatedInputResolver.php
 *
 * @author Matthieu Napoli https://github.com/mnapoli
 * @copyright Copyright (c) Matthieu Napoli
 */
class HyphenatedInputResolver implements ParameterResolver
{
    /**
     * Tries to maps hyphenated parameters to a similarly-named,
     * non-hyphenated parameters in the function signature.
     *
     * E.g. `->call($callable, ['dry-run' => true])` will inject the boolean `true`
     * for a parameter named either `$dryrun` or `$dryRun`.
     *
     * @param \ReflectionFunctionAbstract $reflection
     * @param array                       $providedParameters
     * @param array                       $resolvedParameters
     *
     * @return array
     */
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {
        $parameters = [];

        foreach ($reflection->getParameters() as $index => $parameter) {
            $parameters[mb_strtolower($parameter->name)] = $index;
        }

        foreach ($providedParameters as $name => $value) {
            $normalizedName = mb_strtolower(str_replace('-', '', $name));

            // Skip parameters that do not exist with the normalized name
            if (! array_key_exists($normalizedName, $parameters)) {
                continue;
            }

            $normalizedParameterIndex = $parameters[$normalizedName];

            // Skip parameters already resolved
            if (array_key_exists($normalizedParameterIndex, $resolvedParameters)) {
                continue;
            }

            $resolvedParameters[$normalizedParameterIndex] = $value;
        }

        return $resolvedParameters;
    }
}
