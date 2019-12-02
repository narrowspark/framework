<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Container\Definition;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\Traits\ChangesAwareTrait;
use Viserio\Component\Container\Definition\Traits\MethodCallsAwareTrait;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

final class ReferenceDefinition implements ReferenceDefinitionContract
{
    use MethodCallsAwareTrait {
        addMethodCall as traitAddMethodCall;
    }
    use ChangesAwareTrait;

    /**
     * The definition name.
     *
     * @var string
     */
    private $name;

    /**
     * The PHP type of the identified service.
     *
     * @var null|string
     */
    private $type;

    /**
     * The behavior when the service does not exist.
     *
     * @var int
     */
    private $behavior;

    /**
     * Parameter name used in function.
     *
     * @var null|string
     */
    private $parameterName;

    /**
     * The hash of this definition.
     *
     * @var string
     */
    private $hash;

    /**
     * Create a new Reference Definition instance.
     *
     * @param string $name     The service identifier
     * @param int    $behavior The behavior when the service does not exist
     */
    public function __construct(string $name, int $behavior = 1/* self::EXCEPTION_ON_INVALID_REFERENCE */)
    {
        $this->name = $name;
        $this->behavior = $behavior;
        $this->hash = ContainerBuilder::getHash($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setType(string $type): ReferenceDefinitionContract
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBehavior(): int
    {
        return $this->behavior;
    }

    /**
     * {@inheritdoc}
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * {@inheritdoc}
     */
    public function setVariableName(string $parameterName): ReferenceDefinitionContract
    {
        $this->parameterName = $parameterName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableName(): ?string
    {
        return $this->parameterName;
    }

    /**
     * {@inheritdoc}
     */
    public function addMethodCall(string $method, array $parameters = [], bool $returnsClone = false)
    {
        if (\count($this->methodCalls) >= 1) {
            throw new InvalidArgumentException('A ReferenceDefinition must hold one and only one method call.');
        }

        $this->traitAddMethodCall($method, $parameters, $returnsClone);

        return $this;
    }
}
