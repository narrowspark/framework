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

namespace Viserio\Component\Http\Tests\Constraint;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint as PHPUnitConstraint;

/**
 * Validate contain only the HTTP version number (e.g., "1.1", "1.0").
 */
class HttpProtocolVersion extends PHPUnitConstraint
{
    /** @var string[] */
    protected $validHttpProtocolVersion = [
        '1.0',
        '1.1',
    ];

    /**
     * Asserts protocol version is valid.
     *
     * Protocol version MUST be:
     * - String type.
     * - Valid HTTP protocol version number.
     *
     * @param string $protocolVersion
     * @param string $message
     */
    public static function assertValid($protocolVersion, $message = ''): void
    {
        Assert::assertThat($protocolVersion, new self(), $message);
    }

    public function toString(): string
    {
        return 'is a valid HTTP protocol version number';
    }

    protected function matches($other): bool
    {
        if (! \is_string($other)) {
            return false;
        }

        return \in_array($other, $this->validHttpProtocolVersion, true);
    }
}
