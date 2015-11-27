<?php
namespace Viserio\Container\Definition;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Interop\Container\Definition\DefinitionProviderInterface;

/**
 * ArrayDefinitionProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class ArrayDefinitionProvider implements DefinitionProviderInterface
{
    private $arrayDefinitions;

    public function __construct(array $arrayDefinitions = [])
    {
        $this->arrayDefinitions = $arrayDefinitions;
    }

    /**
     * Implement this method to return the definitions as PHP array.
     *
     * @return array
     */
    protected function getArrayDefinitions()
    {
        return $this->arrayDefinitions;
    }

    public function getDefinitions()
    {
        $definitions = [];

        foreach ($this->getArrayDefinitions() as $identifier => $definition) {
            if ($definition instanceof NamedDefinition) {
                $definition->setIdentifier($identifier);
            } else {
                $definition = new ParameterDefinition($identifier, $definition);
            }

            $definitions[] = $definition;
        }

        return $definitions;
    }
}
