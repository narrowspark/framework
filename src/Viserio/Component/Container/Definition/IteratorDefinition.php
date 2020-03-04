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

namespace Viserio\Component\Container\Definition;

use Traversable;
use Viserio\Contract\Container\Definition\IteratorDefinition as IteratorDefinitionContract;

final class IteratorDefinition extends AbstractDefinition implements IteratorDefinitionContract
{
    /**
     * Default deprecation template.
     *
     * @var string
     */
    protected $defaultDeprecationTemplate = 'The [%s] service is deprecated. You should stop using it, as it will be removed in the future.';

    /**
     * List of parameter to pass when calling the class.
     *
     * @var null|array<int|string, mixed>
     */
    private ?array $argument = null;

    /**
     * Create a new Iterator Definition instance.
     *
     * @param string|Traversable $value
     */
    public function __construct(string $name, $value, int $type)
    {
        parent::__construct($name, $type);

        $this->value = $value;

        if ($value instanceof Traversable) {
            $this->setArgument(\iterator_to_array($value));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getArgument(): ?array
    {
        return $this->argument;
    }

    /**
     * {@inheritdoc}
     */
    public function setArgument(array $argument)
    {
        $this->changes['argument'] = true;

        $this->argument = $argument;

        return $this;
    }
}
