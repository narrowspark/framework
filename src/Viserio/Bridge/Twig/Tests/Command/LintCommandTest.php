<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Viserio\Bridge\Twig\Command\LintCommand;
use Viserio\Component\Support\Invoker;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class LintCommandTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \Symfony\Component\Console\Tester\CommandTester
     */
    private $commandTester;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $contianer = new ArrayContainer([
            'config' => [
                'viserio' => [
                    'view' => [
                    ],
                ],
            ],
            Environment::class => new Environment(new ArrayLoader([])),
        ]);
        $command = new LintCommand();
        $command->setContainer($contianer);
        $command->setInvoker(new Invoker());

        $this->commandTester = new CommandTester($command);
    }

    public function testLintCorrectFile(): void
    {
        $this->commandTester->execute(['dir' => __DIR__ . '/../Fixture', '--files' => ['lintCorrectFile.twig']], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]);

        static::assertContains('OK in', \trim($this->commandTester->getDisplay(true)));
    }

    public function testlintIncorrectFile(): void
    {
        $this->commandTester->execute(['dir' => __DIR__ . '/../Fixture', '--files' => ['lintIncorrectFile.twig']], ['decorated' => false]);

        $file = \realpath(self::normalizeDirectorySeparator(__DIR__ . '/../Fixture/lintIncorrectFile.twig'));

        static::assertSame(
            \preg_replace('/(\r\n|\n\r|\r|\n)/', '', \trim('Fail in ' . self::normalizeDirectorySeparator($file) . ' (line 1)
>> 1      {{ foo
>> Unclosed "variable".
    2
      0 Twig files have valid syntax and 1 contain errors.')),
            \preg_replace('/(\r\n|\n\r|\r|\n)/', '', \trim($this->commandTester->getDisplay(true)))
        );
    }

    public function testLintFilesFound(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->commandTester->execute(['dir' => __DIR__ . '/../Engine'], ['decorated' => false]);
    }

    public function testLint2FileWithFilesArgument(): void
    {
        $this->commandTester->execute(['dir' => __DIR__ . '/../Fixture', '--files' => ['lintCorrectFile.twig', 'lintCorrectFile2.twig']], ['decorated' => false]);

        static::assertSame('All 2 Twig files contain valid syntax.', \trim($this->commandTester->getDisplay(true)));
    }

    public function testLintFileInSubDir(): void
    {
        $this->commandTester->execute(['dir' => __DIR__ . '/../Fixture', '--directories' => ['twig']], ['decorated' => false]);

        static::assertSame('All 2 Twig files contain valid syntax.', \trim($this->commandTester->getDisplay(true)));
    }

    public function testLintFileInSubDirAndFileName(): void
    {
        $this->commandTester->execute(['dir' => __DIR__ . '/../Fixture', '--directories' => ['twig'], '--files' => ['test.twig']], ['decorated' => false]);

        static::assertSame('All 1 Twig files contain valid syntax.', \trim($this->commandTester->getDisplay(true)));
    }

    public function testLintFileInSubDirAndFileNameAndJson(): void
    {
        $this->commandTester->execute(['dir' => __DIR__ . '/../Fixture', '--directories' => ['twig'], '--files' => ['test.twig'], '--format' => 'json'], ['decorated' => false]);

        $file = self::normalizeDirectorySeparator(\dirname(__DIR__) . '/Fixture/twig/test.twig');

        static::assertSame('[
    {
        "file": "' . $file . '",
        "valid": true
    }
]', \trim($this->commandTester->getDisplay(true)));
    }

    public function testLint(): void
    {
        $this->commandTester->execute(['dir' => __DIR__ . '/../Fixture/twig'], ['decorated' => false]);

        static::assertSame('All 2 Twig files contain valid syntax.', \trim($this->commandTester->getDisplay(true)));
    }

    public function testThrowExceptionOnWrongFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The format [test] is not supported.');

        $this->commandTester->execute(['dir' => __DIR__ . '/../Fixture', '--directories' => ['twig'], '--files' => ['test.twig'], '--format' => 'test'], ['decorated' => false]);
    }

    public function testThrowErrorIfTwigIsNotSet(): void
    {
        $command = new LintCommand();
        $command->setContainer(new ArrayContainer());
        $command->setInvoker(new Invoker());

        $commandTester = new CommandTester($command);
        $commandTester->execute(['dir' => __DIR__ . '/../Fixture'], ['decorated' => false]);

        static::assertSame('The Twig environment needs to be set.', \trim($commandTester->getDisplay(true)));
    }
}
