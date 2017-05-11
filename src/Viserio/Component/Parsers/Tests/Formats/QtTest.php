<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use PHPUnit\Framework\TestCase;

class QtTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parsers\Formats\Qt
     */
    private $parser;

    /**
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    private $file;

    public function setUp()
    {
        $this->file   = new Filesystem();
        $this->parser = new Qt();
    }

    public function testParse()
    {
        $datas = $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/qt/resources.ts'));

        self::assertSame(
            [
                [
                    'source' => 'New tweets',
                    'target' => ' This is the master string',
                ],
                [
                    'source' => 'Another tweet',
                    'target' => ' This string is obsolete',
                ],
                [
                    'source' => '%1 subtitle(s) extracted',
                    'target' => '%1 subtitle extracted %1 subtitles extracted',
                ],
            ],
            $datas
        );
    }
}
