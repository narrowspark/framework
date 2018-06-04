<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Extractor;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\Extractor\PhpExtractor;

/**
 * @internal
 */
final class PhpExtractorTest extends TestCase
{
    /**
     * @var \Viserio\Component\Translation\Extractor\PhpExtractor
     */
    private $extractor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor = new PhpExtractor();
    }

    public function testExtractionThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Translation\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The [test] file does not exist.');

        $this->extractor->extract(['test']);
    }

    /**
     * @dataProvider resourcesProvider
     *
     * @param array|string $resource
     */
    public function testExtraction($resource): void
    {
        $this->extractor->setPrefix('prefix');

        $expectedHeredoc = <<<EOF
heredoc key with whitespace and escaped \$\n sequences
EOF;
        $expectedNowdoc = <<<'EOF'
nowdoc key with whitespace and nonescaped \$\n sequences
EOF;
        // Assert
        $expectedCatalogue = [
            'messages' => [
                'single-quoted key'                                                                          => 'prefixsingle-quoted key',
                'double-quoted key'                                                                          => 'prefixdouble-quoted key',
                'heredoc key'                                                                                => 'prefixheredoc key',
                'nowdoc key'                                                                                 => 'prefixnowdoc key',
                "double-quoted key with whitespace and escaped \$\n\" sequences"                             => "prefixdouble-quoted key with whitespace and escaped \$\n\" sequences",
                'single-quoted key with whitespace and nonescaped \$\n\' sequences'                          => 'prefixsingle-quoted key with whitespace and nonescaped \$\n\' sequences',
                'single-quoted key with "quote mark at the end"'                                             => 'prefixsingle-quoted key with "quote mark at the end"',
                $expectedHeredoc                                                                             => 'prefix' . $expectedHeredoc,
                $expectedNowdoc                                                                              => 'prefix' . $expectedNowdoc,
                '{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }' => 'prefix' . '{ gender, select, male {He avoids bugs} female {She avoids bugs} other {They avoid bugs} }',
            ],
            'not_messages' => [
                'other-domain-test-no-params-short-array'            => 'prefixother-domain-test-no-params-short-array',
                'other-domain-test-no-params-long-array'             => 'prefixother-domain-test-no-params-long-array',
                'other-domain-test-params-short-array'               => 'prefixother-domain-test-params-short-array',
                'other-domain-test-params-long-array'                => 'prefixother-domain-test-params-long-array',
                'typecast'                                           => 'prefixtypecast',
            ],
        ];

        $this->assertEquals($expectedCatalogue, $this->extractor->extract($resource));
    }

    public function resourcesProvider()
    {
        $directory = \dirname(__DIR__) . '/Fixture/Extractor/';
        $splFiles  = [];
        $phpFile   = '';

        foreach (new \DirectoryIterator($directory) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->getBasename() === 'translation.html.php') {
                $phpFile = $fileInfo->getPathname();
            }

            $splFiles[] = $fileInfo->getFileInfo();
        }

        return [
            [$directory],
            [$phpFile],
            [\glob($directory . '*')],
            [$splFiles],
            [new ArrayObject(\glob($directory . '*'))],
            [new ArrayObject($splFiles)],
            [new \SplFileInfo($directory . 'translation.html.php')],
        ];
    }
}
