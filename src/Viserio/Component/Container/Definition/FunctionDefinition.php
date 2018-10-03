<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition;

use Psr\Container\ContainerInterface;
use Viserio\Component\Container\Definition\Traits\DefinitionTrait;
use Viserio\Component\Container\Definition\Traits\DeprecationTrait;
use Viserio\Component\Container\Definition\Traits\ResolveParameterClassTrait;
use Viserio\Component\Container\Reflection\ReflectionFactory;
use Viserio\Component\Container\Reflection\ReflectionResolver;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;

/**
 * @internal
 */
final class FunctionDefinition extends ReflectionResolver implements DefinitionContract
{
    use DefinitionTrait;
    use DeprecationTrait;
    use ResolveParameterClassTrait;

    /**
     * Default deprecation template.
     *
     * @var string
     */
    private $defaultDeprecationTemplate = 'The [%s] binding is deprecated. You should stop using it, as it will soon be removed.';

    /**
     * Create a new Factory Definition instance.
     *
     * @param string                $name
     * @param array|callable|string $value
     * @param int                   $type
     */
    public function __construct(string $name, $value, int $type)
    {
        $this->name = $name;
        $this->type = $type;

        $this->reflector  = ReflectionFactory::getFunctionReflector($value);
        $this->parameters = ReflectionFactory::getParameters($this->reflector);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(ContainerInterface $container, array $parameters = []): void
    {
        $this->value = $this->resolveReflectionFunction($this->reflector, $this->parameters, $parameters);

        $this->extend($this->value, $container);

        $this->resolved = true;
    }
}
