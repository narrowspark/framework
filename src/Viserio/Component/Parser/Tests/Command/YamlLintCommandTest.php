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

namespace Viserio\Component\Parser\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Parser\Command\YamlLintCommand;
use Viserio\Component\Support\Invoker;
use Viserio\Contract\Parser\Exception\RuntimeException;

/**
 * Validates Yaml files syntax and outputs encountered errors.
 *
 * Some of this code has been ported from Symfony. The original
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Yaml/Command/LintCommand.php
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * @internal
 *
 * @small
 */
final class YamlLintCommandTest extends TestCase
{
    /** @var \Viserio\Component\Parser\Command\YamlLintCommand */
    private $command;

    /** @var array */
    private $files;

    /** @var string */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = __DIR__ . \DIRECTORY_SEPARATOR . 'yml-lint-test';

        \mkdir($this->path);

        $this->files = [];

        $command = new YamlLintCommand();
        $command->setInvoker(new Invoker());

        $this->command = $command;
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

    public function testLintCommandToThrowRuntimeExceptionOnMissingFileOrSTDIN(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Please provide a filename or pipe file content to STDIN.');

        if ((bool) \getenv('APPVEYOR') || (bool) \getenv('TRAVIS')) {
            self::markTestSkipped('Skipped on Ci.');
        }

        $tester = new CommandTester($this->command);

        $tester->execute(['filename' => ''], []);
    }

    public function testLintCorrectFile(): void
    {
        $tester = new CommandTester($this->command);
        $filename = $this->createFile('foo: bar');

        $ret = $tester->execute(['filename' => $filename], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]);

        self::assertEquals(0, $ret, 'Returns 0 in case of success');
        self::assertRegExp('/^\/\/ OK in /', \trim($tester->getDisplay()));
    }

    public function testLintIncorrectFile(): void
    {
        $incorrectContent = '
foo:
bar';
        $tester = new CommandTester($this->command);
        $filename = $this->createFile($incorrectContent);

        $ret = $tester->execute(['filename' => $filename], ['decorated' => false]);

        self::assertEquals(1, $ret, 'Returns 1 in case of error');
        self::assertStringContainsString('Unable to parse at line 3 (near "bar").', \trim($tester->getDisplay()));
    }

    public function testConstantAsKey(): void
    {
        $yaml = <<<'YAML'
!php/const 'Viserio\Component\Parser\Tests\Command\Foo::TEST': bar
YAML;

        $tester = new CommandTester($this->command);
        $ret = $tester->execute(['filename' => $this->createFile($yaml)], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]);

        self::assertSame(0, $ret, 'lint:yaml exits with code 0 in case of success');
    }

    public function testCustomTags(): void
    {
        $yaml = <<<'YAML'
foo: !my_tag {foo: bar}
YAML;

        $tester = new CommandTester($this->command);
        $ret = $tester->execute(['filename' => $this->createFile($yaml), '--parse-tags' => true], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]);

        self::assertSame(0, $ret, 'lint:yaml exits with code 0 in case of success');
    }

    public function testCustomTagsError(): void
    {
        $yaml = <<<'YAML'
foo: !my_tag {foo: bar}
YAML;

        $tester = new CommandTester($this->command);
        $ret = $tester->execute(['filename' => $this->createFile($yaml)], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false]);

        self::assertSame(1, $ret, 'lint:yaml exits with code 1 in case of error');
    }

    public function testLintFileNotReadable(): void
    {
        $this->expectException(RuntimeException::class);

        $tester = new CommandTester($this->command);
        $filename = $this->createFile('');

        \unlink($filename);

        $tester->execute(['filename' => $filename], ['decorated' => false]);
    }

    /**
     * @param mixed $content
     *
     * @return string Path to the new file
     */
    private function createFile($content): string
    {
        $filename = $this->path . '/sf-';

        \file_put_contents($filename, $content);

        $this->files[] = $filename;

        return $filename;
    }
}

class Foo
{
    public const TEST = 'foo';
}
