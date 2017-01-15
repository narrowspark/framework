<?php
declare(strict_types=1);
namespace Viserio\Exception\Tests\Transformers;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Viserio\Exception\Transformers\CommandLineTransformer;
use Viserio\Exception\Transformers\UndefinedMethodFatalErrorTransformer;
use Symfony\Component\Debug\Exception\UndefinedMethodException;

class UndefinedMethodFatalErrorTransformerTest extends TestCase
{
    public function testExceptionIsWrapped()
    {
        $transformer = new UndefinedMethodFatalErrorTransformer();
        $exception = $transformer->transform(
            new FatalErrorException('Call to undefined method SplObjectStorage::what()', 0, 1, 'foo.php', 12)
        );

        static::assertInstanceOf(
            UndefinedMethodException::class,
            $exception
        );
        static::assertSame('Attempted to call an undefined method named "what" of class "SplObjectStorage".', $exception->getMessage());
    }

    public function testExceptionIsNotWrapped()
    {
        $transformer = new UndefinedMethodFatalErrorTransformer();
        $exception = $transformer->transform(
            new FatalErrorException('', 0, 1, 'foo.php', 12)
        );

        static::assertInstanceOf(
            FatalErrorException::class,
            $exception
        );
        static::assertSame('', $exception->getMessage());
    }
}
