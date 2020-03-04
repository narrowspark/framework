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

use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;

interface DecoratorAwareDefinition
{
    /**
     * Sets the service that this service is decorating.
     *
     * @param string      $id        The decorated service id, use null to remove decoration
     * @param null|string $renamedId The new decorated service id
     * @param int         $priority  The priority of decoration
     * @param int         $behavior  The behavior to adopt when decorated is invalid
     *
     * @throws \Viserio\Contract\Container\Exception\InvalidArgumentException in case the decorated service id and the new decorated service id are equals
     *
     * @return static
     */
    public function decorate(
        string $id,
        ?string $renamedId = null,
        int $priority = 0,
        int $behavior = ReferenceDefinitionContract::EXCEPTION_ON_INVALID_REFERENCE
    );

    /**
     * Remove decorator from definition.
     */
    public function removeDecorator(): void;

    /**
     * Gets the service that this service is decorating.
     *
     * @return null|array An array composed of the decorated service id, the new id for it and the priority of decoration, null if no service is decorated
     */
    public function getDecorator(): ?array;
}
