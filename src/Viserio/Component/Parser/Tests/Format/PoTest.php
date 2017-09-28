<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Format;

use PHPUnit\Framework\TestCase;
use Throwable;
use Viserio\Component\Parser\Parser\PoParser;

class PoTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parser\Parser\PoParser
     */
    private $parser;

    /**
     * @var string
     */
    private $fixturePath;

    public function setUp()
    {
        parent::setUp();

        $this->parser      = new PoParser();
        $this->fixturePath = __DIR__ . '/../Fixtures/po';
    }

    public function testRead()
    {
        try {
            $result = $this->parser->parse(self::readFile($this->fixturePath . '/healthy.po'));
        } catch (Throwable $e) {
            $result = [];
            var_dump($e);
            $this->fail($e->getMessage());
        }

        self::assertCount(2, $result);

        // Read file without headers.
        // It should not skip first entry
        try {
            $result = $this->parser->parse(self::readFile($this->fixturePath . '/noheader.po'));
        } catch (Throwable $e) {
            $result = [];
            $this->fail($e->getMessage());
        }

        self::assertCount(2, $result, 'Did not read properly po file without headers.');
    }

    private static function readFile(string $filePath): string
    {
        return file_get_contents($filePath);
    }
}
