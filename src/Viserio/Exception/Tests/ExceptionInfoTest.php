<?php
namespace Viserio\Exception\Tests;

use Viserio\Exception\ExceptionInfo;

class ExceptionInfoTest extends \PHPUnit_Framework_TestCase
{
    public function testBadError()
    {
        $info = (new ExceptionInfo())->generate('test', 666);

        $expected = [
            'id' => 'test',
            'code' => 500,
            'name' => 'Internal Server Error',
            'detail' => 'An error has occurred and this resource cannot be displayed.',
            'summary' => 'Houston, We Have A Problem.'
        ];

        $this->assertSame($expected, $info);
    }

    public function testHiddenError()
    {
        $info = (new ExceptionInfo())->generate('hi', 503);

        $expected = [
            'id' => 'hi',
            'code' => 503,
            'name' => 'Service Unavailable',
            'detail' => 'The server is currently unavailable. It may be overloaded or down for maintenance.',
            'summary' => 'Houston, We Have A Problem.'
        ];

        $this->assertSame($expected, $info);
    }
}
