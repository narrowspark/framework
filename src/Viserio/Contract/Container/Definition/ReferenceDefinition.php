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

namespace Viserio\Contract\Container\Definition;

use Viserio\Contract\Container\Definition\ChangeAwareDefinition as ChangeAwareDefinitionContract;
use Viserio\Contract\Container\Definition\MethodCallsAwareDefinition as MethodCallsAwareDefinitionContract;

interface ReferenceDefinition extends ChangeAwareDefinitionContract, MethodCallsAwareDefinitionContract
{
    public const RUNTIME_EXCEPTION_ON_INVALID_REFERENCE = 0;
    public const NULL_ON_INVALID_REFERENCE = 1;
    public const IGNORE_ON_UNINITIALIZED_REFERENCE = 2;
    public const IGNORE_ON_INVALID_REFERENCE = 3;
    public const DELEGATE_REFERENCE = 4;

    /**
     * Get the targeting name.
     *
     * @return string
     */
    public function getName(): ?string;

    /**
     * Get the PHP type of the identified service.
     *
     * @return null|string
     */
    public function getType(): ?string;

    /**
     * Set the PHP type of the identified service.
     *
     * @param string $type
     *
     * @return static
     */
    public function setType(string $type): self;

    /**
     * Returns the behavior to be used when the service does not exist.
     *
     * @return int
     */
    public function getBehavior(): int;

    /**
     * Get the definition hash.
     *
     * @return string
     */
    public function getHash(): string;

    /**
     * Return a variable name of this reference.
     *
     * @param string $parameterName
     *
     * @return static
     */
    public function setVariableName(string $parameterName): self;

    /**
     * Return a variable name of this reference.
     *
     * @return null|string
     */
    public function getVariableName(): ?string;
}
