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
