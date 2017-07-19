<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\Commands;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\View\ViewFinder;
use Viserio\Provider\Twig\Command\LintCommand;
use Viserio\Provider\Twig\Loader;

class LintCommandTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function testLintCorrectFile(): void
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['--files' => ['lintCorrectFile']], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]);

        self::assertContains('OK in', \trim($tester->getDisplay(true)));
    }

    public function testLintIncorrectFile(): void
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['--files' => ['lintIncorrectFile']], ['decorated' => false]);
        $file     = \realpath($this->normalizeDirectorySeparator(__DIR__ . '/../Fixtures/lintIncorrectFile.twig'));

        self::assertSame(
            \preg_replace('/(\r\n|\n\r|\r|\n)/', '', \trim('Fail in ' . $this->normalizeDirectorySeparator($file) . ' (line 1)
>> 1      {{ foo
>> Unclosed "variable".
    2
      0 Twig files have valid syntax and 1 contain errors.')),
            \preg_replace('/(\r\n|\n\r|\r|\n)/', '', \trim($tester->getDisplay(true)))
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No twig files found.
     */
    public function testLintFilesFound(): void
    {
        $tester   = $this->createCommandTester(__DIR__ . '/../Engine');
        $ret      = $tester->execute([], ['decorated' => false]);
    }

    public function testLint2FileWithFilesArgument(): void
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['--files' => ['lintCorrectFile', 'lintCorrectFile2']], ['decorated' => false]);

        self::assertSame('All 2 Twig files contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    public function testLintFileInSubDir(): void
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['--directories' => ['twig']], ['decorated' => false]);

        self::assertSame('All 2 Twig files contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    public function testLintFileInSubDirAndFileName(): void
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['--directories' => ['twig'], '--files' => ['test']], ['decorated' => false]);

        self::assertSame('All 1 Twig files contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    public function testLintFileInSubDirAndFileNameAndJson(): void
    {
        $tester = $this->createCommandTester();
        $ret    = $tester->execute(['--directories' => ['twig'], '--files' => ['test'], '--format' => 'json'], ['decorated' => false]);
        $file   = $this->normalizeDirectorySeparator(\realpath(__DIR__ . '/../Fixtures/twig/test.twig'));

        self::assertSame('[
    {
        "file": "' . $file . '",
        "valid": true
    }
]', \trim($tester->getDisplay(true)));
    }

    public function testLint(): void
    {
        $tester   = $this->createCommandTester(__DIR__ . '/../Fixtures/twig');
        $ret      = $tester->execute([], ['decorated' => false]);

        self::assertSame('All 2 Twig files contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The format [test] is not supported.
     */
    public function testThrowExceptionOnWrongFormat(): void
    {
        $tester   = $this->createCommandTester(__DIR__ . '/../Fixtures/twig');

        $tester->execute(['--format' => 'test'], ['decorated' => false]);
    }

    public function testThrowErrorIfTwigIsNotSet(): void
    {
        $config = [
            'config' => [
                'viserio' => [
                    'view' => [
                        'paths' => [
                            $path ?? __DIR__ . '/../Fixtures/',
                        ],
                    ],
                ],
            ],
        ];
        $finder = new ViewFinder(new Filesystem(), new ArrayContainer($config));
        $loader = new Loader($finder);
        $twig   = new Environment($loader);

        $application = new Application();
        $application->setContainer(new ArrayContainer(
            \array_merge(
                $config,
                [
                    FinderContract::class       => $finder,
                    LoaderInterface::class      => $loader,
                ]
            )
        ));
        $application->add(new LintCommand());

        $tester = new CommandTester($application->find('twig:lint'));

        $tester->execute([], ['decorated' => false]);

        self::assertSame('The Twig environment needs to be set.', \trim($tester->getDisplay(true)));
    }

    /**
     * @param null|mixed $path
     *
     * @return CommandTester
     */
    private function createCommandTester($path = null)
    {
        $config = [
            'config' => [
                'viserio' => [
                    'view' => [
                        'paths' => [
                            $path ?? __DIR__ . '/../Fixtures/',
                        ],
                    ],
                ],
            ],
        ];
        $finder = new ViewFinder(new Filesystem(), new ArrayContainer($config));
        $loader = new Loader($finder);
        $twig   = new Environment($loader);

        $application = new Application();
        $application->setContainer(new ArrayContainer(
            \array_merge(
                $config,
                [
                        Environment::class          => $twig,
                        FinderContract::class       => $finder,
                        LoaderInterface::class      => $loader,
                ]
            )
        ));
        $application->add(new LintCommand());

        return new CommandTester($application->find('twig:lint'));
    }
}
