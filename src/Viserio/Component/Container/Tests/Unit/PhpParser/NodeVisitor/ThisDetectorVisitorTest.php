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

namespace Viserio\Component\Container\Tests\Unit\PhpParser\NodeVisitor;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\PhpParser\NodeVisitor\ThisDetectorVisitor;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\PhpParser\NodeVisitor\ThisDetectorVisitor
 *
 * @small
 */
final class ThisDetectorVisitorTest extends TestCase
{
    public function testThisIsDiscovered(): void
    {
        $visitor = new ThisDetectorVisitor();
        $visitor->leaveNode(new Variable('this'));

        self::assertTrue($visitor->detected);
    }

    public function testThisIsNotDiscovered(): void
    {
        $visitor = new ThisDetectorVisitor();
        $visitor->leaveNode(new Variable('foo'));

        self::assertFalse($visitor->detected);
    }

    public function testThisIsNotDiscoveredWithNonVariable(): void
    {
        $visitor = new ThisDetectorVisitor();
        $visitor->leaveNode(new Closure());

        self::assertFalse($visitor->detected);
    }
}
