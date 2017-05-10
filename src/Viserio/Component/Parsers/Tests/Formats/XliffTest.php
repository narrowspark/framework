<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parsers\Formats\Xliff;

class XliffTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parsers\Formats\Xliff
     */
    private $parser;

    /**
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    private $file;

    public function setUp()
    {
        $this->file   = new Filesystem();
        $this->parser = new Xliff();
    }

    public function testParseXliffV1()
    {
        $datas = $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/xliff/xliffv1.xlf'));

        self::assertSame(unserialize($this->file->read(__DIR__ . '/../Fixtures/xliff/output_xliffv1.xlf')), $datas);
    }

    public function testParseXliffV2()
    {
        $datas = $this->parser->parse((string) $this->file->read(__DIR__ . '/../Fixtures/xliff/xliffv2.xlf'));

        self::assertSame(unserialize($this->file->read(__DIR__ . '/../Fixtures/xliff/output_xliffv2.xlf')), $datas);
    }
}
