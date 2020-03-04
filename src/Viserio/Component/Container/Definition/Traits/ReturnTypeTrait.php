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

trait ReturnTypeTrait
{
    /** @var string */
    private $returnType;

    /**
     * {@inheritdoc}
     */
    public function getReturnType(): ?string
    {
        return $this->returnType;
    }

    /**
     * {@inheritdoc}
     */
    public function setReturnType(string $type)
    {
        $this->returnType = $type;

        return $this;
    }
}
