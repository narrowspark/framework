<?php
namespace Viserio\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Parsers\Formats\QueryStr;

class QueryStrTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Viserio\Parsers\Formats\QueryStr
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new QueryStr();
    }

    public function testParse()
    {
        // $parsed = $this->parser->parse(
        //     'to=Jack Smith&from=Jane Doe&subject=Hello World&body=Hello, whats going on...'
        // );

        // $this->assertTrue(is_array($parsed));
        // $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $parsed);
    }
}
