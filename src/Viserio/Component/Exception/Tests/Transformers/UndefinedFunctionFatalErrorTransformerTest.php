<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Transformers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\UndefinedFunctionException;
use Viserio\Component\Exception\Transformers\UndefinedFunctionFatalErrorTransformer;

class UndefinedFunctionFatalErrorTransformerTest extends TestCase
{
    public function testExceptionIsWrapped()
    {
        $transformer = new UndefinedFunctionFatalErrorTransformer();
        $exception   = $transformer->transform(
            new FatalErrorException('Call to undefined function test_namespaced_function()', 0, 1, 'foo.php', 12)
        );

        static::assertInstanceOf(
            UndefinedFunctionException::class,
            $exception
        );
        static::assertSame('Attempted to call function "test_namespaced_function" from the global namespace.', $exception->getMessage());
    }

    public function testExceptionIsNotWrapped()
    {
        $transformer = new UndefinedFunctionFatalErrorTransformer();
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
