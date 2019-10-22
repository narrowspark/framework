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
