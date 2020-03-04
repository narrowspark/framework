<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Container\Definition;

use Viserio\Contract\Container\Definition\ChangeAwareDefinition as ChangeAwareDefinitionContract;
use Viserio\Contract\Container\Definition\MethodCallsAwareDefinition as MethodCallsAwareDefinitionContract;

interface ReferenceDefinition extends ChangeAwareDefinitionContract, MethodCallsAwareDefinitionContract
{
    public const RUNTIME_EXCEPTION_ON_INVALID_REFERENCE = 0;
    public const EXCEPTION_ON_INVALID_REFERENCE = 1;
    public const NULL_ON_INVALID_REFERENCE = 2;
    public const IGNORE_ON_UNINITIALIZED_REFERENCE = 3;
    public const IGNORE_ON_INVALID_REFERENCE = 4;
    public const DELEGATE_REFERENCE = 5;

    /**
     * Get the targeting name.
     *
     * @return string
     */
    public function getName(): ?string;

    /**
     * Get the PHP type of the identified service.
     */
    public function getType(): ?string;

    /**
     * Set the PHP type of the identified service.
     *
     * @return static
     */
    public function setType(string $type): self;

    /**
     * Returns the behavior to be used when the service does not exist.
     */
    public function getBehavior(): int;

    /**
     * Get the definition hash.
     */
    public function getHash(): string;

    /**
     * Return a variable name of this reference.
     *
     * @return static
     */
    public function setVariableName(string $parameterName): self;

    /**
     * Return a variable name of this reference.
     */
    public function getVariableName(): ?string;
}
