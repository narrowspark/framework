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
use Viserio\Component\Console\Application;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class LintCommandTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function testLintCorrectFile()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['dir' => __DIR__ . '\..\Fixtures', '--files' => ['lintCorrectFile.twig']], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]);

        self::assertContains('OK in', trim($tester->getDisplay(true)));
    }

    public function testLintIncorrectFile()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['dir' => __DIR__ . '\..\Fixtures', '--files' => ['lintIncorrectFile.twig']], ['decorated' => false]);
        $file     = $this->normalizeDirectorySeparator(realpath(__DIR__ . '\..\Fixtures\lintIncorrectFile.twig'));

        self::assertSame(
            preg_replace('/(\r\n|\n\r|\r|\n)/', '', trim('Fail in ' . $file . ' (line 1)
>> 1      {{ foo
>> Unclosed "variable".
    2
      0 Twig files have valid syntax and 1 contain errors.')),
            preg_replace('/(\r\n|\n\r|\r|\n)/', '', trim($tester->getDisplay(true)))
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLintFilesFound()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['dir' => __DIR__ . '\..\Engine'], ['decorated' => false]);
    }

    public function testLint2FileWithFilesArgument()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['dir' => __DIR__ . '\..\Fixtures', '--files' => ['lintCorrectFile.twig', 'lintCorrectFile2.twig']], ['decorated' => false]);

        self::assertSame('All 2 Twig files contain valid syntax.', trim($tester->getDisplay(true)));
    }

    public function testLintFileInSubDir()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['dir' => __DIR__ . '\..\Fixtures', '--directories' => ['twig']], ['decorated' => false]);

        self::assertSame('All 2 Twig files contain valid syntax.', trim($tester->getDisplay(true)));
    }

    public function testLintFileInSubDirAndFileName()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['dir' => __DIR__ . '\..\Fixtures', '--directories' => ['twig'], '--files' => ['test.twig']], ['decorated' => false]);

        self::assertSame('All 1 Twig files contain valid syntax.', trim($tester->getDisplay(true)));
    }

    public function testLintFileInSubDirAndFileNameAndJson()
    {
        $tester = $this->createCommandTester();
        $ret    = $tester->execute(['dir' => __DIR__ . '\..\Fixtures', '--directories' => ['twig'], '--files' => ['test.twig'], '--format' => 'json'], ['decorated' => false]);
        $file   = $this->normalizeDirectorySeparator(realpath(__DIR__ . '\..\Fixtures\twig\test.twig'));

        self::assertSame('[
    {
        "file": "' . $file . '",
        "valid": true
    }
]', trim($tester->getDisplay(true)));
    }

    public function testLint()
    {
        $tester = $this->createCommandTester();
        $ret    = $tester->execute(['dir' => __DIR__ . '\..\Fixtures\twig'], ['decorated' => false]);

        self::assertSame('All 2 Twig files contain valid syntax.', trim($tester->getDisplay(true)));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The format [test] is not supported.
     */
    public function testThrowExceptionOnWrongFormat()
    {
        $path   = __DIR__ . '\..\Fixtures\twig';
        $tester = $this->createCommandTester();

        $tester->execute(['dir' => __DIR__ . '\..\Fixtures', '--directories' => ['twig'], '--files' => ['test.twig'], '--format' => 'test'], ['decorated' => false]);
    }

    public function testThrowErrorIfTwigIsNotSet()
    {
        $application = new Application();
        $application->setContainer(new ArrayContainer());
        $application->add(new LintCommand());

        $tester = new CommandTester($application->find('twig:lint'));

        $tester->execute(['dir' => __DIR__ . '\..\Fixtures'], ['decorated' => false]);

        self::assertSame('The Twig environment needs to be set.', trim($tester->getDisplay(true)));
    }

    /**
     * @return CommandTester
     */
    private function createCommandTester()
    {
        $application = new Application();
        $application->setContainer(new ArrayContainer([
            'config' => [
                'viserio' => [
                    'view' => [
                    ],
                ],
            ],
            Environment::class => new Environment(new ArrayLoader([])),
        ]));
        $application->add(new LintCommand());

        return new CommandTester($application->find('twig:lint'));
    }
}
