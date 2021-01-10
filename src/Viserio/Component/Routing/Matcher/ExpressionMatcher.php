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

class ExpressionMatcher extends AbstractMatcher
{
    /**
     * The expression string.
     *
     * @var string
     */
    protected $expression;

    /**
     * Create a new expression segment matcher instance.
     */
    public function __construct(string $expression, array $parameterKeys)
    {
        $this->expression = $expression;
        $this->parameterKeys = $parameterKeys;
    }

    /**
     * Returns the used expression.
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionExpression(string $segmentVariable, ?int $uniqueKey = null): string
    {
        return \str_replace('{segment}', $segmentVariable, $this->expression);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMatchHash(): string
    {
        return $this->expression;
    }
}
