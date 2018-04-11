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
 */
class XliffLintCommandTest extends TestCase
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
}
