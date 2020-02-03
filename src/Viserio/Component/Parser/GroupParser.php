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

namespace Viserio\Component\Parser;

class GroupParser extends Parser
{
    /**
     * Key for grouping.
     *
     * @var string
     */
    private $groupKey;

    /**
     * Set group key.
     *
     * @param string $key
     *
     * @return $this
     */
    public function setGroup(string $key): self
    {
        $this->groupKey = $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if ($this->groupKey === null) {
            return parent::parse($payload);
        }

        return [$this->groupKey => parent::parse($payload)];
    }
}
