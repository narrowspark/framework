<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition;

use Viserio\Component\Container\Definition\Traits\DeprecationTrait;
use Viserio\Component\Contract\Container\Compiler\DeprecatedDefinition as DeprecatedDefinitionContract;

/**
 * @internal
 */
final class AliasDefinition implements DeprecatedDefinitionContract
{
    use DeprecationTrait;

    /**
     * Default deprecation template.
     *
     * @var string
     */
    private $defaultDeprecationTemplate = 'The [%s] binding alias is deprecated. You should stop using it, as it will soon be removed.';

    /**
     * The alias name.
     *
     * @var string
     */
    private $name;

    /**
     * The binding name.
     *
     * @var string
     */
    private $definitionName;

    /**
     * Create a new AliasDefinition instance.
     *
     * @param string $alias
     * @param string $name
     */
    public function __construct(string $alias, string $name)
    {
        $this->name           = $alias;
        $this->definitionName = $name;
    }

    /**
     * Get the binding name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->definitionName;
    }

    /**
     * Get the alias name.
     *
     * @return string
     */
    public function getAlias(): string
    {
        return $this->name;
    }
}
