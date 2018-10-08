<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\Commands;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Twig\Environment;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Support\Invoker;
use Viserio\Component\View\ViewFinder;
use Viserio\Provider\Twig\Command\LintCommand;
use Viserio\Provider\Twig\Loader;

/**
 * @internal
 */
final class LintCommandTest extends MockeryTestCase
{
    /**
     * @var string
     */
    private $fixturePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturePath = \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Fixture';
    }

    public function testLintCorrectFile(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['--files' => ['lintCorrectFile']], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]);

        static::assertContains('OK in', \trim($tester->getDisplay(true)));
    }

    public function testLintIncorrectFile(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['--files' => ['lintIncorrectFile']], ['decorated' => false]);
        $file = \realpath($this->fixturePath . \DIRECTORY_SEPARATOR . 'lintIncorrectFile.twig');

        static::assertSame(
            \preg_replace('/(\r\n|\n\r|\r|\n)/', '', \trim('Fail in ' . $file . ' (line 1)
>> 1      {{ foo
>> Unclosed "variable".
    2
      0 Twig files have valid syntax and 1 contain errors.')),
            \preg_replace('/(\r\n|\n\r|\r|\n)/', '', \trim($tester->getDisplay(true)))
        );
    }

    public function testLintFilesFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No twig files found.');

        $tester = $this->createCommandTester(\dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Engine');
        $tester->execute([], ['decorated' => false]);
    }

    public function testLint2FileWithFilesArgument(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['--files' => ['lintCorrectFile', 'lintCorrectFile2']], ['decorated' => false]);

        static::assertSame('All 2 Twig files contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    public function testLintFileInSubDir(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['--directories' => ['twig']], ['decorated' => false]);

        static::assertSame('All 2 Twig files contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    public function testLintFileInSubDirAndFileName(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['--directories' => ['twig'], '--files' => ['test']], ['decorated' => false]);

        static::assertSame('All 1 Twig files contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    public function testLintFileInSubDirAndFileNameAndJson(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['--directories' => ['twig'], '--files' => ['test'], '--format' => 'json'], ['decorated' => false]);
        $file = $this->fixturePath . \DIRECTORY_SEPARATOR . 'twig' . \DIRECTORY_SEPARATOR . 'test.twig';

        static::assertSame('[
    {
        "file": "' . $file . '",
        "valid": true
    }
]', \trim($tester->getDisplay(true)));
    }

    public function testLint(): void
    {
        $tester = $this->createCommandTester($this->fixturePath . \DIRECTORY_SEPARATOR . 'twig');
        $tester->execute([], ['decorated' => false]);

        static::assertSame('All 2 Twig files contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    public function testThrowExceptionOnWrongFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The format [test] is not supported.');

        $tester = $this->createCommandTester($this->fixturePath . \DIRECTORY_SEPARATOR . 'twig');

        $tester->execute(['--format' => 'test'], ['decorated' => false]);
    }

    /**
     * @param null|mixed $path
     *
     * @return \Symfony\Component\Console\Tester\CommandTester
     */
    private function createCommandTester($path = null): CommandTester
    {
        $config = $this->arrangeConfig($path);

        $finder = new ViewFinder(new Filesystem(), $config['config']);
        $loader = new Loader($finder);

        $command = new LintCommand(new Environment($loader), $finder, $config['config']);
        $command->setContainer(new ArrayContainer([]));
        $command->setInvoker(new Invoker());

        return new CommandTester($command);
    }

    /**
     * @param null|string $path
     *
     * @return array
     */
    private function arrangeConfig(?string $path = null): array
    {
        return [
            'config' => [
                'viserio' => [
                    'view' => [
                        'paths' => [
                            $path ?? $this->fixturePath . \DIRECTORY_SEPARATOR,
                        ],
                    ],
                ],
            ],
        ];
    }
}
