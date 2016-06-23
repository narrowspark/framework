<?php
namespace Viserio\Exception\Tests\Transformers;

use Exception;
use ErrorException;
use Viserio\Exception\Transformers\CommandLineTransformer;

class CommandLineTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleErrorsReturnsValidErrorMessage()
    {
        $exception = new ErrorException('test message', 0, E_ERROR, 'test.php', 15);

        $transformer = new CommandLineTransformer();
        $format = $transformer->transform($exception);

        $expected = "\n+-------------+\n| FATAL ERROR |\n+-------------+\ntest message in test.php on line 15\n";

        $this->assertEquals($expected, $format->getMessage());
    }

    public function testHandleExceptionsReturnsValidErrorMessage()
    {
        $exception = new Exception('test message');

        $transformer = new CommandLineTransformer();
        $format = $transformer->transform($exception);

        $this->assertTrue(strlen($format->getMessage()) > 0);
        $this->assertTrue((strpos($format->getMessage(), 'UNHANDLED EXCEPTION') !== false));
    }
}
