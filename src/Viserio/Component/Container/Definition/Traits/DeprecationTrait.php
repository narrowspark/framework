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

use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @property null|string $deprecationTemplate
 *
 * @internal
 */
trait DeprecationTrait
{
    /**
     * Check if the definition is deprecated.
     *
     * @var bool
     */
    protected $deprecated = false;

    /**
     * {@inheritdoc}
     */
    public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    /**
     * {@inheritdoc}
     */
    public function setDeprecated(bool $status = true, ?string $template = null)
    {
        if ($template !== null) {
            if (\strpos($template, '%s') === false) {
                throw new InvalidArgumentException('The deprecation template must contain the [%s] placeholder.');
            }

            $this->deprecationTemplate = $template;
        }

        $this->deprecated = $status;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeprecationMessage(): string
    {
        return \sprintf($this->deprecationTemplate ?? $this->defaultDeprecationTemplate, $this->name);
    }
}
