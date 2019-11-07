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

namespace Viserio\Component\Routing\Matcher;

class AnyMatcher extends AbstractMatcher
{
    /**
     * Create a new any matcher instance.
     *
     * @param array $parameterKeys
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
