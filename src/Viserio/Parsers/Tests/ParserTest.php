<?php
namespace Viserio\Parsers\Formats\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Filesystem;
use Viserio\Parsers\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    private $parser;

    public function setUp()
    {
        $this->parser = new Parser(new Filesystem());
    }

    public function testGetFilesystem()
    {
        $this->assertInstanceOf('Viserio\Contracts\Filesystem\Filesystem', $this->parser->getFilesystem());
    }

    public function testGetParser()
    {
        $this->assertInstanceOf('Viserio\Parsers\Formats\INI', $this->parser->getParser('ini'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\JSON', $this->parser->getParser('json'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\JSON', $this->parser->getParser('application/json'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\JSON', $this->parser->getParser('application/x-javascript'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\JSON', $this->parser->getParser('text/javascript'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\JSON', $this->parser->getParser('text/x-javascript'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\JSON', $this->parser->getParser('text/x-json'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\PHP', $this->parser->getParser('php'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\Serialize', $this->parser->getParser('application/vnd.php.serialized'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\QueryStr', $this->parser->getParser('application/x-www-form-urlencoded'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\TOML', $this->parser->getParser('toml'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\XML', $this->parser->getParser('xml'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\XML', $this->parser->getParser('application/xml'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\XML', $this->parser->getParser('text/xml'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\YAML', $this->parser->getParser('yaml'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\YAML', $this->parser->getParser('text/yaml'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\YAML', $this->parser->getParser('text/x-yaml'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\YAML', $this->parser->getParser('application/yaml'));
        $this->assertInstanceOf('Viserio\Parsers\Formats\YAML', $this->parser->getParser('application/x-yaml'));
    }
}
