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

namespace Viserio\Component\Routing\Matcher;

class AnyMatcher extends AbstractMatcher
{
    /**
     * Create a new any matcher instance.
     */
    public function __construct(array $parameterKeys)
    {
        $this->parameterKeys = $parameterKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionExpression(string $segmentVariable, ?int $uniqueKey = null): string
    {
        return $segmentVariable . ' !== \'\'';
    }

    /**
     * {@inheritdoc}
     */
    protected function getMatchHash(): string
    {
        return '';
    }
}
