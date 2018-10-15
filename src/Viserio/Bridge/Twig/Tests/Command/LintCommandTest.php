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

/**
 * @internal
 */
final class LintCommandTest extends MockeryTestCase
{
    /**
     * @var \Symfony\Component\Console\Tester\CommandTester
     */
    private $commandTester;

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
        $contianer         = new ArrayContainer([
            'config' => [
                'viserio' => [
                    'view' => [
                    ],
                ],
            ],
        ]);
        $command = new LintCommand(new Environment(new ArrayLoader([])));
        $command->setContainer($contianer);
        $command->setInvoker(new Invoker());

        $this->commandTester = new CommandTester($command);
    }

    public function testLintCorrectFile(): void
    {
        $this->commandTester->execute(['dir' => $this->fixturePath, '--files' => ['lintCorrectFile.twig']], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]);

        static::assertContains('OK in', \trim($this->commandTester->getDisplay(true)));
    }

    public function testLintIncorrectFile(): void
    {
        $this->commandTester->execute(['dir' => $this->fixturePath, '--files' => ['lintIncorrectFile.twig']], ['decorated' => false]);

        $file = $this->fixturePath . \DIRECTORY_SEPARATOR . 'lintIncorrectFile.twig';

        static::assertSame(
            'Fail in ' . \realpath($file) . ' (line 1)
>> 1      {{ foo
>> Unclosed "variable". 
   2      
0 Twig files have valid syntax and 1 contain errors.
',
            $this->commandTester->getDisplay(true)
        );
    }

    public function testLintFilesFound(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->commandTester->execute(['dir' => $this->fixturePath . \DIRECTORY_SEPARATOR . 'Engine'], ['decorated' => false]);
    }

    public function testLint2FileWithFilesArgument(): void
    {
        $this->commandTester->execute(['dir' => $this->fixturePath, '--files' => ['lintCorrectFile.twig', 'lintCorrectFile2.twig']], ['decorated' => false]);

        static::assertSame('All 2 Twig files contain valid syntax.', \trim($this->commandTester->getDisplay(true)));
    }

    public function testLintFileInSubDir(): void
    {
        $this->commandTester->execute(['dir' => $this->fixturePath, '--directories' => ['twig']], ['decorated' => false]);

        static::assertSame('All 2 Twig files contain valid syntax.', \trim($this->commandTester->getDisplay(true)));
    }

    public function testLintFileInSubDirAndFileName(): void
    {
        $this->commandTester->execute(['dir' => $this->fixturePath, '--directories' => ['twig'], '--files' => ['test.twig']], ['decorated' => false]);

        static::assertSame('All 1 Twig files contain valid syntax.', \trim($this->commandTester->getDisplay(true)));
    }

    public function testLintFileInSubDirAndFileNameAndJson(): void
    {
        $this->commandTester->execute(['dir' => $this->fixturePath, '--directories' => ['twig'], '--files' => ['test.twig'], '--format' => 'json'], ['decorated' => false]);

        $file = $this->fixturePath . \DIRECTORY_SEPARATOR . 'twig' . \DIRECTORY_SEPARATOR . 'test.twig';

        static::assertSame(
            \json_encode([['file' => $file, 'valid' => true]], \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES),
            \preg_replace('/\\\\/', '\\', \trim($this->commandTester->getDisplay(true)))
        );
    }

    public function testLint(): void
    {
        $this->commandTester->execute(['dir' => $this->fixturePath . \DIRECTORY_SEPARATOR . 'twig'], ['decorated' => false]);

        static::assertSame('All 2 Twig files contain valid syntax.', \trim($this->commandTester->getDisplay(true)));
    }

    public function testThrowExceptionOnWrongFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The format [test] is not supported.');

        $this->commandTester->execute(['dir' => $this->fixturePath, '--directories' => ['twig'], '--files' => ['test.twig'], '--format' => 'test'], ['decorated' => false]);
    }
}
