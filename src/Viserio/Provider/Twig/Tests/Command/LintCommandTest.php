<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Provider\Twig\Tests\Commands;

use InvalidArgumentException;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use RuntimeException;
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
 *
 * @small
 */
final class LintCommandTest extends MockeryTestCase
{
    /** @var string */
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

        self::assertStringContainsString('OK in', \trim($tester->getDisplay(true)));
    }

    public function testLintIncorrectFile(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['--files' => ['lintIncorrectFile']], ['decorated' => false]);
        $file = \realpath($this->fixturePath . \DIRECTORY_SEPARATOR . 'lintIncorrectFile.twig');

        self::assertSame(
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
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No twig files found.');

        $tester = $this->createCommandTester(\dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Engine');
        $tester->execute([], ['decorated' => false]);
    }

    public function testLint2FileWithFilesArgument(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['--files' => ['lintCorrectFile', 'lintCorrectFile2']], ['decorated' => false]);

        self::assertSame('2 Twig files contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    public function testLintFileInSubDir(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['--directories' => ['twig']], ['decorated' => false]);

        self::assertSame('2 Twig files contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    public function testLintFileInSubDirAndFileName(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['--directories' => ['twig'], '--files' => ['test']], ['decorated' => false]);

        self::assertSame('1 Twig file contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    public function testLintFileInSubDirAndFileNameAndJson(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['--directories' => ['twig'], '--files' => ['test'], '--format' => 'json'], ['decorated' => false]);
        $file = $this->fixturePath . \DIRECTORY_SEPARATOR . 'twig' . \DIRECTORY_SEPARATOR . 'test.twig';

        self::assertSame(
            \json_encode([['file' => $file, 'valid' => true]], \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES),
            \trim($tester->getDisplay(true))
        );
    }

    public function testLint(): void
    {
        $tester = $this->createCommandTester($this->fixturePath . \DIRECTORY_SEPARATOR . 'twig');
        $tester->execute([], ['decorated' => false]);

        self::assertSame('2 Twig files contain valid syntax.', \trim($tester->getDisplay(true)));
    }

    public function testThrowExceptionOnWrongFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The format [test] is not supported.');

        $tester = $this->createCommandTester($this->fixturePath . \DIRECTORY_SEPARATOR . 'twig');

        $tester->execute(['--format' => 'test'], ['decorated' => false]);
    }

    /**
     * @group legacy
     */
    public function testTwigLintWithDeprecations(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['--files' => ['deprecations.twig'], '--show-deprecations' => true], ['decorated' => false]);

        $file = $this->fixturePath . \DIRECTORY_SEPARATOR . 'deprecations.twig';

        self::assertStringContainsString('Fail in ' . \realpath($file) . ' (line -1)
   1      {% deprecated \'test is deprecated\' %}', \trim($tester->getDisplay(true)));
    }

    /**
     * @param null|mixed $path
     *
     * @return \Symfony\Component\Console\Tester\CommandTester
     */
    private function createCommandTester($path = null): CommandTester
    {
        $config = $this->arrangeConfig($path);

        $finder = new ViewFinder($config['config']);
        $loader = new Loader($finder, new Filesystem());

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
