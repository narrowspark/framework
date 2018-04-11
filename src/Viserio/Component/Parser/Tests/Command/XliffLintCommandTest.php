<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Parser\Command\XliffLintCommand;

/**
 * Validates XLIFF files syntax and outputs encountered errors.
 *
 * Some of this code has been ported from Symfony. The original
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Translation/Tests/Command/XliffLintCommandTest.php
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */
class XliffLintCommandTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parser\Command\XliffLintCommand
     */
    private $command;

    /**
     * @var array
     */
    private $files;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        \mkdir(\sys_get_temp_dir() . '/xliff-lint-test');

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

        \rmdir(\sys_get_temp_dir() . '/xliff-lint-test');
    }

    /**
     * @expectedException \Viserio\Component\Contract\Translation\Exception\RuntimeException
     * @expectedExceptionMessage Please provide a filename or pipe file content to STDIN.
     */
    public function testLintCommandToThrowRuntimeExceptionOnMissingFileOrSTDIN(): void
    {
        $tester = new CommandTester($this->command);

        $tester->execute([], []);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Translation\Exception\InvalidArgumentException
     * @expectedExceptionMessage The format "test" is not supported.
     */
    public function testLintCommandToThrowException(): void
    {
        $tester = new CommandTester($this->command);

        $tester->execute(['--format' => 'test', 'filename' => __DIR__ . '/../Fixtures/xliff/encoding_xliff_v1.xlf'], []);
    }

    public function testLintCommandCorrectXliffV1File(): void
    {
        $tester = new CommandTester($this->command);

        $tester->execute(
            ['filename' => __DIR__ . '/../Fixtures/xliff/encoding_xliff_v1.xlf'],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]
        );

        self::assertEquals(0, $tester->getStatusCode(), 'Returns 0 in case of success');
        self::assertContains('OK', \trim($tester->getDisplay()));
    }

    public function testLintCommandCorrectXliffV2File(): void
    {
        $tester = new CommandTester($this->command);

        $tester->execute(
            ['filename' => __DIR__ . '/../Fixtures/xliff/encoding_xliff_v2.xlf'],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]
        );

        self::assertEquals(0, $tester->getStatusCode(), 'Returns 0 in case of success');
        self::assertContains('OK', \trim($tester->getDisplay()));
    }

    public function testLintCommandWithXliffDir(): void
    {
        $tester = new CommandTester($this->command);

        $tester->execute(
            ['filename' => __DIR__ . '/../Fixtures/xliffCommand'],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]
        );

        self::assertEquals(0, $tester->getStatusCode(), 'Returns 0 in case of success');
    }

    public function testLintCommandWithEmptyXliffDir(): void
    {
        $tester = new CommandTester($this->command);

        $dirPath = __DIR__ . '/../Fixtures/empty';

        \mkdir($dirPath);
        \touch($dirPath . '/test.txt');

        $tester->execute(
            ['filename' => $dirPath],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]
        );

        self::assertEquals(0, $tester->getStatusCode(), 'Returns 0 in case of success');

        \unlink($dirPath . '/test.txt');
        \rmdir($dirPath);
    }

//    public function testLintCommandIncorrectXmlSyntax(): void
//    {
//        $tester   = new CommandTester($this->command);
//        $filename = $this->createFile('note <target>');
//
//        $tester->execute(['filename' => $filename], ['decorated' => false]);
//
//        self::assertEquals(1, $tester->getStatusCode(), 'Returns 1 in case of error');
//        self::assertContains('Opening and ending tag mismatch: target line 6 and source', \trim($tester->getDisplay()));
//    }
//
//    public function testLintCommandIncorrectXmlSyntaxWithJsonFormat(): void
//    {
//        $tester   = new CommandTester($this->command);
//        $filename = $this->createFile('note <target>');
//
//        $tester->execute(['filename' => $filename, '--format' => 'json'], ['decorated' => false]);
//
//        self::assertEquals(1, $tester->getStatusCode(), 'Returns 1 in case of error');
//        self::assertContains('Opening and ending tag mismatch: target line 6 and source', \trim($tester->getDisplay()));
//
//        \json_decode(\trim($tester->getDisplay()));
//
//        self::assertTrue(json_last_error() == JSON_ERROR_NONE);
//    }
//
//    public function testLintCommandIncorrectTargetLanguage(): void
//    {
//        $tester   = new CommandTester($this->command);
//        $filename = $this->createFile('note', 'es');
//
//        $tester->execute(['filename' => $filename], ['decorated' => false]);
//
//        self::assertEquals(1, $tester->getStatusCode(), 'Returns 1 in case of error');
//        self::assertContains('There is a mismatch between the file extension ("en.xlf") and the "es" value used in the "target-language" attribute of the file.', \trim($tester->getDisplay()));
//    }
//
//    /**
//     * @expectedException \Viserio\Component\Contract\Translation\Exception\RuntimeException
//     */
//    public function testLintCommandFileNotReadable(): void
//    {
//        $tester   = new CommandTester($this->command);
//        $filename = $this->createFile();
//
//        \unlink($filename);
//
//        $tester->execute(['filename' => $filename], ['decorated' => false]);
//    }
}
