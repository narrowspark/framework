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

namespace Viserio\Component\Http\Tests\Response\Traits;

trait StreamBodyContentCasesTrait
{
    /**
     * @return iterable<array<string, mixed>>
     */
    protected function getNonStreamBodyContentCases(): iterable
    {
        yield ['null' => null];

        yield ['true' => true];

        yield ['false' => false];

        yield ['zero' => 0];

        yield ['int' => 1];

        yield ['zero-float' => 0.0];

        yield ['float' => 1.1];

        yield ['array' => ['php://temp']];

        yield ['object' => (object) ['php://temp']];
    }
}
