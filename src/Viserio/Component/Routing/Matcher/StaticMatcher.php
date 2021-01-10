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

use Viserio\Contract\Routing\Exception\InvalidArgumentException;

class StaticMatcher extends AbstractMatcher
{
    /**
     * The static string.
     *
     * @var string
     */
    protected $segment;

    /**
     * Create a new satic segment matcher instance.
     *
     * @throws \Viserio\Contract\Routing\Exception\InvalidArgumentException
     */
    public function __construct(string $segment, ?array $parameterKeys = null)
    {
        if (\strpos($segment, '/') !== false) {
            throw new InvalidArgumentException(\sprintf('Cannot create %s: segment cannot contain \'/\', \'%s\' given.', __CLASS__, $segment));
        }

        $this->parameterKeys = $parameterKeys ?? [];
        $this->segment = $segment;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionExpression(string $segmentVariable, ?int $uniqueKey = null): string
    {
        return $segmentVariable . ' === \'' . $this->segment . '\'';
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchedParameterExpressions(string $segmentVariable, ?int $uniqueKey = null): array
    {
        $keys = $this->parameterKeys;

        if (\count($keys) > 0) {
            return [$keys[0] => $segmentVariable];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function getMatchHash(): string
    {
        return $this->segment;
    }
}
