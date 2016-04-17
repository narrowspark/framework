<?php
namespace Viserio\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Parsers\Formats\Serialize;

class SerializeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Viserio\Parsers\Formats\Serialize
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new Serialize();
    }

    public function testParse()
    {
        // $parsed = $this->parser->parse(
        //     'a:1:{s:7:"message";a:4:{s:2:"to";s:10:"Jack Smith";s:4:"from";s:8:"Jane Doe";s:7:"subject";s:11:"Hello World";s:4:"body";s:24:"Hello, whats going on...";}}'
        // );

        // $this->assertTrue(is_array($parsed));
        // $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $parsed);
    }
}
