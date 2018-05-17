<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Viserio\Component\Parser\Parser\YamlParser;

class YamlTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testParseWithSetFlags(): void
    {
        $parser = new YamlParser();

        $parsed = $parser->parse('foo: 2016-05-27');

        self::assertNotInstanceOf(\DateTime::class, $parsed['foo']);

        $parser->setFlags(Yaml::PARSE_DATETIME);
        $parsed = $parser->parse('foo: 2016-05-27');

        self::assertInternalType('array', $parsed);
        self::assertInstanceOf(\DateTime::class, $parsed['foo']);
    }

    public function testParse(): void
    {
        $file = vfsStream::newFile('temp.yaml')->withContent(
            '
preset: psr2

risky: false

linting: true
            '
        )->at($this->root);

        $parsed = (new YamlParser())->parse(\file_get_contents($file->url()));

        self::assertInternalType('array', $parsed);
        self::assertSame(['preset' => 'psr2', 'risky' => false, 'linting' => true], $parsed);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Parser\Exception\ParseException
     * @expectedExceptionMessage Unable to parse at line 3 (near "  foo: bar").
     */
    public function testParseToThrowException(): void
    {
        $file = vfsStream::newFile('temp.yaml')->withContent(
            '
collection:
-  key: foo
  foo: bar
            '
        )->at($this->root);

        (new YamlParser())->parse(\file_get_contents($file->url()));
    }
}
