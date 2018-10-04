<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition;

use Psr\Container\ContainerInterface;
use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Container\Compiler\CompileHelper;
use Viserio\Component\Container\Definition\Traits\DefinitionTrait;
use Viserio\Component\Container\Definition\Traits\DeprecationTrait;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;

/**
 * @internal
 */
final class ArrayDefinition implements DefinitionContract
{
    use DefinitionTrait;
    use DeprecationTrait;

    /**
     * Default deprecation template.
     *
     * @var string
     */
    protected $defaultDeprecationTemplate = 'The [%s] binding is deprecated. You should stop using it, as it will soon be removed.';

    /**
     * Create a new Array Definition instance.
     *
     * @param string $name
     * @param array  $value
     * @param int    $type
     */
    public function __construct(string $name, array $value, int $type)
    {
        $this->name = $name;
        $this->type = $type;

        $this->value = \array_map(function ($value) {
            if ($value instanceof DefinitionContract) {
                return $value->getValue();
            }

            return $value;
        }, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(ContainerInterface $container, array $parameters = []): void
    {
        $this->extend($this->value, $container);

        $this->resolved = true;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(): string
    {
        $compiledBinding = VarExporter::export($this->value);

        if ($this->isExtended()) {
            return CompileHelper::compileExtend(
                $this->extenders,
                $compiledBinding,
                $this->extendMethodName,
                $this->isShared(),
                $this->getName()
            );
        }

        return CompileHelper::printReturn($compiledBinding, $this->isShared(), $this->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function getDebugInfo(): string
    {
        return VarExporter::export($this->value);
    }
}
