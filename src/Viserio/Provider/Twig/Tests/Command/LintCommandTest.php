<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\Commands;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Viserio\Component\Contract\View\Finder as FinderContract;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Support\Invoker;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\View\ViewFinder;
use Viserio\Provider\Twig\Command\LintCommand;
use Viserio\Provider\Twig\Loader;

/**
 * @internal
 */
final class LintCommandTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

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
        $file = \realpath(self::normalizeDirectorySeparator(__DIR__ . '/../Fixture/lintIncorrectFile.twig'));

        static::assertSame(
            \preg_replace('/(\r\n|\n\r|\r|\n)/', '', \trim('Fail in ' . self::normalizeDirectorySeparator($file) . ' (line 1)
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

        $tester = $this->createCommandTester(__DIR__ . '/../Engine');
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
        $file = self::normalizeDirectorySeparator(\realpath(__DIR__ . '/../Fixture/twig/test.twig'));

        static::assertSame('[
    {
        "file": "' . $file . '",
        "valid": true
    }
]', \trim($tester->getDisplay(true)));
    }

    public function testLint(): void
    {
        $tester = $this->createCommandTester(__DIR__ . '/../Fixture/twig');
        $tester->execute([], ['decorated' => false]);

        static::assertSame('All 2 Twig files contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    public function testThrowExceptionOnWrongFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The format [test] is not supported.');

        $tester = $this->createCommandTester(__DIR__ . '/../Fixture/twig');

        $tester->execute(['--format' => 'test'], ['decorated' => false]);
    }

    public function testThrowErrorIfTwigIsNotSet(): void
    {
        $config    = $this->arrangeConfig();
        $finder    = new ViewFinder(new Filesystem(), $config['config']);
        $loader    = new Loader($finder);
        $container = new ArrayContainer(
            \array_merge(
                $config,
                [
                    FinderContract::class  => $finder,
                    LoaderInterface::class => $loader,
                ]
            )
        );

        $command = new LintCommand();
        $command->setContainer($container);
        $command->setInvoker(new Invoker());

        $tester = new CommandTester($command);

        $tester->execute([], ['decorated' => false]);

        static::assertSame('The Twig environment needs to be set.', \trim($tester->getDisplay(true)));
    }

    /**
     * @param null|mixed $path
     *
     * @return \Symfony\Component\Console\Tester\CommandTester
     */
    private function createCommandTester($path = null): CommandTester
    {
        $config = $this->arrangeConfig($path);

        $finder    = new ViewFinder(new Filesystem(), $config['config']);
        $loader    = new Loader($finder);
        $container = new ArrayContainer(
            \array_merge(
                $config,
                [
                    Environment::class     => new Environment($loader),
                    FinderContract::class  => $finder,
                    LoaderInterface::class => $loader,
                ]
            )
        );

        $command = new LintCommand();
        $command->setContainer($container);
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
                            $path ?? __DIR__ . '/../Fixture/',
                        ],
                    ],
                ],
            ],
        ];
    }
}
