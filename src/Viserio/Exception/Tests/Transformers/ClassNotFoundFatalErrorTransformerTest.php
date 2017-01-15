<?php
declare(strict_types=1);
namespace Viserio\Exception\Tests\Transformers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Viserio\Exception\Transformers\ClassNotFoundFatalErrorTransformer;

class ClassNotFoundFatalErrorTransformerTest extends TestCase
{
    public function testExceptionIsWrapped()
    {
        $transformer = new ClassNotFoundFatalErrorTransformer();
        $exception   = $transformer->transform(
            new FatalErrorException('Class \'WhizBangFactory\' not found', 0, 1, 'foo.php', 12)
        );

        static::assertInstanceOf(
            ClassNotFoundException::class,
            $exception
        );
        static::assertSame('Attempted to load class "WhizBangFactory" from the global namespace.
Did you forget a "use" statement?', $exception->getMessage());
    }

    public function testExceptionIsNotWrapped()
    {
        $transformer = new ClassNotFoundFatalErrorTransformer();
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
