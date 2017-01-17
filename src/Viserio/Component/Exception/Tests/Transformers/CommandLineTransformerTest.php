<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Transformers;

use ErrorException;
use Exception;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Transformers\CommandLineTransformer;

class CommandLineTransformerTest extends TestCase
{
    public function testHandleErrorsReturnsValidErrorMessage()
    {
        $exception = new ErrorException('test message', 0, E_ERROR, 'test.php', 15);

        $transformer = new CommandLineTransformer();
        $format      = $transformer->transform($exception);

        $expected = "\n+-------------+\n| FATAL ERROR |\n+-------------+\ntest message in test.php on line 15\n";

        self::assertEquals($expected, $format->getMessage());
    }

    public function testHandleExceptionsReturnsValidErrorMessage()
    {
        $exception = new Exception('test message');

        $transformer = new CommandLineTransformer();
        $format      = $transformer->transform($exception);

        self::assertTrue(mb_strlen($format->getMessage()) > 0);
        self::assertTrue((mb_strpos($format->getMessage(), 'UNHANDLED EXCEPTION') !== false));
    }
}
