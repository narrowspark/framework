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

namespace Viserio\Component\Exception\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\UndefinedMethodException;
use Viserio\Component\Exception\Transformer\UndefinedMethodFatalErrorTransformer;

/**
 * @internal
 *
 * @small
 */
final class UndefinedMethodFatalErrorTransformerTest extends TestCase
{
    public function testExceptionIsWrapped(): void
    {
        $transformer = new UndefinedMethodFatalErrorTransformer();
        $exception = $transformer->transform(
            new FatalErrorException('Call to undefined method SplObjectStorage::what()', 0, 1, 'foo.php', 12)
        );

        self::assertInstanceOf(
            UndefinedMethodException::class,
            $exception
        );
        self::assertSame('Attempted to call an undefined method named "what" of class "SplObjectStorage".', $exception->getMessage());
    }

    public function testExceptionIsNotWrapped(): void
    {
        $transformer = new UndefinedMethodFatalErrorTransformer();
        $exception = $transformer->transform(
            new FatalErrorException('', 0, 1, 'foo.php', 12)
        );

        self::assertInstanceOf(
            FatalErrorException::class,
            $exception
        );
        self::assertSame('', $exception->getMessage());
    }
}
