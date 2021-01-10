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

namespace Viserio\Component\Container\Definition\Traits;

use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @property array<string, bool> $changes
 *
 * @internal
 */
trait DecoratorAwareTrait
{
    /**
     * @internal
     *
     * The inner service id
     *
     * @var null|string
     */
    public $innerServiceId;

    /**
     * @internal
     *
     * Used to store the behavior to follow when using service decoration and the decorated service is invalid
     *
     * @var null|string
     */
    public $decorationOnInvalid;

    /**
     * The decorated service data.
     *
     * @var null|array
     */
    protected $decoratedService;

    /**
     * {@inheritdoc}
     */
    public function decorate(
        string $id,
        ?string $renamedId = null,
        int $priority = 0,
        int $behavior = 1/* ReferenceDefinitionContract::EXCEPTION_ON_INVALID_REFERENCE */
    
    ) {
        if ($renamedId && $id === $renamedId) {
            throw new InvalidArgumentException(\sprintf('The decorated service inner name for [%s] must be different than the service name itself.', $id));
        }

        $this->changes['decorated_service'] = true;

        $this->decoratedService = [$id, $renamedId, $priority];

        if ($behavior !== 1/* ReferenceDefinitionContract::EXCEPTION_ON_INVALID_REFERENCE */) {
            $this->decoratedService[] = $behavior;
        } else {
            $this->decoratedService[] = null;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeDecorator(): void
    {
        $this->decoratedService = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDecorator(): ?array
    {
        return $this->decoratedService;
    }
}
