<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\TaggableParser;

/**
 * @internal
 */
final class TaggableParserTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Parser\TaggableParser
     */
    private $parser;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root   = vfsStream::setup();
        $this->parser = new TaggableParser();
    }

    public function testParse(): void
    {
        $file = vfsStream::newFile('temp.json')->withContent(
            '
{
    "a":1,
    "e":5
}
            '
        )->at($this->root);

        $parsed = $this->parser->parse($file->url());

        $this->assertInternalType('array', $parsed);
        $this->assertSame(['a' => 1, 'e' => 5], $parsed);
    }

    public function testParseTag(): void
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

        $this->assertInternalType('array', $parsed);
        $this->assertSame(['foo::a' => 1, 'foo::e' => 5], $parsed);
    }
}
