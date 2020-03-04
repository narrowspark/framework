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
