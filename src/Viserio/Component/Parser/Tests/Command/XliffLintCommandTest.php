<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Parser\Command\XliffLintCommand;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * Validates XLIFF files syntax and outputs encountered errors.
 *
 * Some of this code has been ported from Symfony. The original
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Translation/Tests/Command/XliffLintCommandTest.php
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * @internal
 */
final class XliffLintCommandTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \Viserio\Component\Parser\Command\XliffLintCommand
     */
    private $command;

    /**
     * @var array
     */
    private $files;

    /**
     * @var string
     */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = self::normalizeDirectorySeparator(__DIR__ . '/xliff-lint-test');

        \mkdir($this->path);

        $this->files   = [];
        $this->command = new XliffLintCommand();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (\file_exists($file)) {
                \unlink($file);
            }
        }

        \rmdir($this->path);
    }

    public function testLintCommandToThrowRuntimeExceptionOnMissingFileOrSTDIN(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Please provide a filename or pipe file content to STDIN.');

        if ((bool) \getenv('APPVEYOR') || (bool) \getenv('TRAVIS')) {
            $this->markTestSkipped('Skipped on Ci.');
        }

        $tester = new CommandTester($this->command);

        $tester->execute(['filename' => ''], []);
    }

    public function testLintCommandToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The format [test] is not supported.');

        $tester = new CommandTester($this->command);

        $tester->execute(['--format' => 'test', 'filename' => __DIR__ . '/../Fixture/xliff/encoding_xliff_v1.xlf'], []);
    }

    public function testLintCommandCorrectXliffV1File(): void
    {
        $tester = new CommandTester($this->command);

        $tester->execute(
            ['filename' => __DIR__ . '/../Fixture/xliff/encoding_xliff_v1.xlf'],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]
        );

        $this->assertEquals(0, $tester->getStatusCode(), 'Returns 0 in case of success');
        $this->assertContains('OK', \trim($tester->getDisplay()));
    }

    public function testLintCommandCorrectXliffV2File(): void
    {
        $tester = new CommandTester($this->command);

        $tester->execute(
            ['filename' => __DIR__ . '/../Fixture/xliff/encoding_xliff_v2.xlf'],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]
        );

        $this->assertEquals(0, $tester->getStatusCode(), 'Returns 0 in case of success');
        $this->assertContains('OK', \trim($tester->getDisplay()));
    }

    public function testLintCommandWithXliffDir(): void
    {
        $tester = new CommandTester($this->command);

        $tester->execute(
            ['filename' => __DIR__ . '/../Fixture/xliffCommand'],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]
        );

        $this->assertEquals(0, $tester->getStatusCode(), 'Returns 0 in case of success');
    }

    public function testLintCommandWithEmptyXliffDir(): void
    {
        $tester = new CommandTester($this->command);

        $dirPath = __DIR__ . '/../Fixture/empty';

        \mkdir($dirPath);
        \touch($dirPath . '/test.txt');

        $tester->execute(
            ['filename' => $dirPath],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]
        );

        $this->assertEquals(0, $tester->getStatusCode(), 'Returns 0 in case of success');

        \unlink($dirPath . '/test.txt');
        \rmdir($dirPath);
    }

    public function testLintCommandIncorrectXmlSyntax(): void
    {
        $tester   = new CommandTester($this->command);
        $filename = $this->createFile('note <target>');

        $tester->execute(['filename' => $filename], ['decorated' => false]);

        $this->assertEquals(1, $tester->getStatusCode(), 'Returns 1 in case of error');
        $this->assertContains('Opening and ending tag mismatch: target line 6 and source', \trim($tester->getDisplay()));
    }

    public function testLintCommandIncorrectXmlSyntaxWithJsonFormat(): void
    {
        $tester   = new CommandTester($this->command);
        $filename = $this->createFile('note <target>');

        $tester->execute(['filename' => $filename, '--format' => 'json'], ['decorated' => false]);

        $this->assertEquals(1, $tester->getStatusCode(), 'Returns 1 in case of error');
        $this->assertContains('Opening and ending tag mismatch: target line 6 and source', \trim($tester->getDisplay()));

        \json_decode(\trim($tester->getDisplay()));

        $this->assertTrue(\json_last_error() === \JSON_ERROR_NONE);
    }

    public function testLintCommandIncorrectTargetLanguage(): void
    {
        $tester   = new CommandTester($this->command);
        $filename = $this->createFile('note', 'es');

        $tester->execute(['filename' => $filename], ['decorated' => false]);

        $this->assertEquals(1, $tester->getStatusCode(), 'Returns 1 in case of error');
        $this->assertContains('There is a mismatch between the file extension [en.xlf] and the [es] value used in the "target-language" attribute of the file.', \trim($tester->getDisplay()));
    }

    public function testLintCommandFileNotReadable(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\RuntimeException::class);

        $tester   = new CommandTester($this->command);
        $filename = $this->createFile();

        \unlink($filename);

        $tester->execute(['filename' => $filename], ['decorated' => false]);
    }

    /**
     * @param string $sourceContent
     * @param string $targetLanguage
     *
     * @return string Path to the new file
     */
    private function createFile(string $sourceContent = 'note', string $targetLanguage = 'en')
    {
        $xliffContent = '<?xml version="1.0"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" target-language="' . $targetLanguage . '" datatype="plaintext" original="file.ext">
        <body>
            <trans-unit id="note">
                <source>' . $sourceContent . '</source>
                <target>NOTE</target>
            </trans-unit>
        </body>
    </file>
</xliff>
';
        $filename = self::normalizeDirectorySeparator($this->path . '/messages.en.xlf');

        \file_put_contents($filename, $xliffContent);

        $this->files[] = $filename;

        return $filename;
    }
}
