<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\UndefinedFunctionException;
use Viserio\Component\Exception\Transformer\UndefinedFunctionFatalErrorTransformer;

class UndefinedFunctionFatalErrorTransformerTest extends TestCase
{
    public function testExceptionIsWrapped()
    {
        $transformer = new UndefinedFunctionFatalErrorTransformer();
        $exception   = $transformer->transform(
            new FatalErrorException('Call to undefined function test_namespaced_function()', 0, 1, 'foo.php', 12)
        );

        self::assertInstanceOf(
            UndefinedFunctionException::class,
            $exception
        );
        self::assertSame('Attempted to call function "test_namespaced_function" from the global namespace.', $exception->getMessage());
    }

    public function testExceptionIsNotWrapped()
    {
        $transformer = new UndefinedFunctionFatalErrorTransformer();
        $exception   = $transformer->transform(
            new FatalErrorException('', 0, 1, 'foo.php', 12)
        );

        self::assertInstanceOf(
            FatalErrorException::class,
            $exception
        );
        self::assertSame('', $exception->getMessage());
    }
}
