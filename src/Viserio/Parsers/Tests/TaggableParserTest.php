<?php
namespace Viserio\Parsers\Formats\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Filesystem;
use Viserio\Parsers\Formats\Php;
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
        $this->parser = new TaggableParser(new Php());
    }

    public function testParseGroup()
    {
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
return ["a" => 1, "e" => 5,];
            '
        )->at($this->root);

        $parsed = $this->parser->parse($file->url(), 'foo');

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['foo::a' => 1, 'foo::e' => 5], $parsed);
    }
}
