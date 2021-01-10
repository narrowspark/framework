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

class Immutable extends PHPUnitConstraint
{
    /** @var object */
    private $new;

    public function __construct(object $new)
    {
        $this->new = $new;
    }

    /**
     * Asserts two objects are the same type but not the same instance.
     *
     * @param string $message
     */
    public static function assertImmutable(object $original, object $new, $message = ''): void
    {
        Assert::assertThat($new, new self($original), $message);
    }

    public function toString(): string
    {
        return 'is immutable';
    }

    protected function matches($other): bool
    {
        if (! ($other instanceof $this->new)) {
            return false;
        }

        return $other !== $this->new;
    }

    protected function failureDescription($other): string
    {
        return \sprintf(
            '%s and %s are different instances of the same class',
            \get_class($this->new),
            \get_class($other)
        );
    }
}
