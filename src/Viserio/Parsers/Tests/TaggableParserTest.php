<?php
declare(strict_types=1);
namespace Viserio\Parsers\Tests;

use org\bovigo\vfs\vfsStream;
use Viserio\Parsers\TaggableParser;

class TaggableParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Parsers\TaggableParser
     */
    private $format;

    public function setUp()
    {
        $this->root   = vfsStream::setup();
        $this->parser = new TaggableParser();
    }

    public function testParseGroup()
    {
        $file = vfsStream::newFile('temp.json')->withContent(
            '
{
    "a":1,
    "e":5
}
            '
        )->at($this->root);

        $parsed = $this->parser->setTag('foo')->parse($file->url());

        self::assertTrue(is_array($parsed));
        self::assertSame(['foo::a' => 1, 'foo::e' => 5], $parsed);
    }
}
