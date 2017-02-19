<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Commands;

use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Twig_Environment;
use Twig_LoaderInterface;
use Viserio\Bridge\Twig\Commands\LintCommand;
use Viserio\Bridge\Twig\Loader;
use Viserio\Component\Console\Application;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\View\ViewFinder;

class LintCommandTest extends TestCase
{
    use MockeryTrait;
    use NormalizePathAndDirectorySeparatorTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testLintCorrectFile()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['--files' => ['lintCorrectFile']], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]);

        self::assertContains('OK in', trim($tester->getDisplay()));
    }

    public function testLintIncorrectFile()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['--files' => ['lintIncorrectFile']], ['decorated' => false]);
        $file     = $this->normalizeDirectorySeparator(realpath(__DIR__ . '/../Fixtures/lintIncorrectFile.twig'));

        self::assertSame(
            preg_replace('/(\r\n|\n\r|\r|\n)/', '', trim('Fail in ' . $file . ' (line 1)
>> 1      {{ foo
>> Unclosed "variable".
    2
      0 Twig files have valid syntax and 1 contain errors.')),
            preg_replace('/(\r\n|\n\r|\r|\n)/', '', trim($tester->getDisplay()))
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No twig files found.
     */
    public function testLintFilesFound()
    {
        $tester   = $this->createCommandTester(__DIR__ . '/../Engine');
        $ret      = $tester->execute([], ['decorated' => false]);
    }

    public function testLint2FileWithFilesArgument()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['--files' => ['lintCorrectFile', 'lintCorrectFile2']], ['decorated' => false]);

        self::assertSame('All 2 Twig files contain valid syntax.', trim($tester->getDisplay()));
    }

    public function testLintFileInSubDir()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['--directories' => ['twig']], ['decorated' => false]);

        self::assertSame('All 2 Twig files contain valid syntax.', trim($tester->getDisplay()));
    }

    public function testLintFileInSubDirAndFileName()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['--directories' => ['twig'], '--files' => ['test']], ['decorated' => false]);

        self::assertSame('All 1 Twig files contain valid syntax.', trim($tester->getDisplay()));
    }

    public function testLintFileInSubDirAndFileNameAndJson()
    {
        $tester = $this->createCommandTester();
        $ret    = $tester->execute(['--directories' => ['twig'], '--files' => ['test'], '--format' => 'json'], ['decorated' => false]);
        $file   = $this->normalizeDirectorySeparator(realpath(__DIR__ . '/../Fixtures/twig/test.twig'));

        self::assertSame('[
    {
        "file": "' . $file . '",
        "valid": true
    }
]', trim($tester->getDisplay()));
    }

    public function testLint()
    {
        $tester   = $this->createCommandTester(__DIR__ . '/../Fixtures/twig');
        $ret      = $tester->execute([], ['decorated' => false]);

        self::assertSame('All 2 Twig files contain valid syntax.', trim($tester->getDisplay()));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The format "test" is not supported.
     */
    public function testThrowExceptionOnWrongFormat()
    {
        $tester   = $this->createCommandTester(__DIR__ . '/../Fixtures/twig');

        $tester->execute(['--format' => 'test'], ['decorated' => false]);
    }

    public function testThrowErrorIfTwigIsNotSet()
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
        $twig   = new Twig_Environment($loader);

        $application = new Application(
            new ArrayContainer(
                array_merge(
                    $config,
                    [
                        FinderContract::class       => $finder,
                        Twig_LoaderInterface::class => $loader,
                    ]
                )
            ),
            '1'
        );
        $application->add(new LintCommand());

        $tester = new CommandTester($application->find('twig:lint'));

        $tester->execute([], ['decorated' => false]);

        self::assertSame('The Twig environment needs to be set.', trim($tester->getDisplay()));
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
                            $path !== null ? $path : __DIR__ . '/../Fixtures/',
                        ],
                    ],
                ],
            ],
        ];
        $finder = new ViewFinder(new Filesystem(), new ArrayContainer($config));
        $loader = new Loader($finder);
        $twig   = new Twig_Environment($loader);

        $application = new Application(
            new ArrayContainer(
                array_merge(
                    $config,
                    [
                        Twig_Environment::class     => $twig,
                        FinderContract::class       => $finder,
                        Twig_LoaderInterface::class => $loader,
                    ]
                )
            ),
            '1'
        );
        $application->add(new LintCommand());

        return new CommandTester($application->find('twig:lint'));
    }
}
