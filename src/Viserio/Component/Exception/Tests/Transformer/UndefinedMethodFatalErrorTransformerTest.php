<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\UndefinedMethodException;
use Viserio\Component\Exception\Transformer\UndefinedMethodFatalErrorTransformer;

/**
 * @internal
 */
final class UndefinedMethodFatalErrorTransformerTest extends TestCase
{
    public function testExceptionIsWrapped(): void
    {
        $transformer = new UndefinedMethodFatalErrorTransformer();
        $exception   = $transformer->transform(
            new FatalErrorException('Call to undefined method SplObjectStorage::what()', 0, 1, 'foo.php', 12)
        );

        static::assertInstanceOf(
            UndefinedMethodException::class,
            $exception
        );
        static::assertSame('Attempted to call an undefined method named "what" of class "SplObjectStorage".', $exception->getMessage());
    }

    public function testExceptionIsNotWrapped(): void
    {
        $transformer = new UndefinedMethodFatalErrorTransformer();
        $exception   = $transformer->transform(
            new FatalErrorException('', 0, 1, 'foo.php', 12)
        );

        static::assertInstanceOf(
            FatalErrorException::class,
            $exception
        );
        static::assertSame('', $exception->getMessage());
    }
}
