<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\OptionsResolver\Command\OptionDumpCommand;
use Viserio\Component\Parser\Dumper;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class OptionDumpCommandTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\OptionsResolver\Command\OptionDumpCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->root    = vfsStream::setup();
        $this->command = new class() extends OptionDumpCommand {
            use NormalizePathAndDirectorySeparatorTrait;

            /**
             * {@inheritdoc}
             */
            protected function getComposerVendorPath(): string
            {
                return self::normalizeDirectorySeparator(\dirname(__DIR__) . '/Fixtures/composer');
            }
        };
    }

    public function testCommandWithNoDirArgument(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute([], ['interactive' => false]);

        self::assertEquals(
            'Argument [dir] can\'t be empty.',
            \trim($tester->getDisplay())
        );
    }

    /**
     * @expectedException \Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException
     * @expectedExceptionMessage Config directory [vfs://bar] cannot be created or is write protected.
     */
    public function testCommandCantCreateDir(): void
    {
        $dir = vfsStream::newDirectory('bar', 0000);

        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $dir->url()], ['interactive' => false]);

        self::assertEquals(
            'Argument [dir] can\'t be empty.',
            \trim($tester->getDisplay())
        );
    }

    public function testCommandWithMerge(): void
    {
        $tester = new CommandTester($this->command);
        $eol    = PHP_EOL;

        vfsStream::newFile('package.php')
            ->withContent("<?php
declare(strict_types=1);

return [
    'vendor' => [
        'package' => [
            'minLength' => 2,
        ],
    ],
];")
            ->at($this->root);

        $tester->execute(['dir' => $this->root->url(), '--merge' => true], ['interactive' => false]);

        self::assertEquals(
            "Searching for php classes with implemented \Viserio\Component\Contract\OptionsResolver\RequiresConfig interface.{$eol} 0/1 [>---------------------------]   0%{$eol} 1/1 [============================] 100%",
            \trim($tester->getDisplay())
        );
        self::assertEquals(
            "<?php
declare(strict_types=1);

return [
    'vendor' => [
        'package' => [
            'minLength' => 2,
            'maxLength' => NULL,
        ],
    ],
];",
            $this->root->getChild('package.php')->getContent()
        );
    }

    public function testCommandWithShow(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $this->root->url(), '--show' => true], ['interactive' => false]);
        $eol = PHP_EOL;

        self::assertEquals(
            "Searching for php classes with implemented \Viserio\Component\Contract\OptionsResolver\RequiresConfig interface.{$eol} 0/1 [>---------------------------]   0%{$eol} 1/1 [============================] 100%{$eol}Output array:\n\n<?php\ndeclare(strict_types=1);\n\nreturn [\n    'vendor' => [\n        'package' => [\n            'minLength' => 2,\n            'maxLength' => NULL,\n        ],\n    ],\n];",
            \trim($tester->getDisplay())
        );
    }

    public function testCommandWithDirArgument(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $this->root->url()], ['interactive' => false]);
        $tester->getDisplay();

        self::assertEquals(
            "<?php
declare(strict_types=1);

return [
    'vendor' => [
        'package' => [
            'minLength' => 2,
            'maxLength' => NULL,
        ],
    ],
];",
            $this->root->getChild('package.php')->getContent()
        );
    }

    public function testCommandShowError(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $this->root->url(), '--format' => 'json'], ['interactive' => false]);

        $output = $tester->getDisplay(true);

        self::assertSame("Only the php format is supported; use composer req viserio/parser to get [json], [xml], [yml] output.\n", $output);
    }

    public function testCommandWithDumper(): void
    {
        $container = new ArrayContainer([Dumper::class => new Dumper()]);
        $command   = $this->command;
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute(['dir' => $this->root->url()], ['interactive' => false]);
        $tester->getDisplay();

        self::assertEquals(
            "<?php
declare(strict_types=1);

return [
    'vendor' => [
        'package' => [
            'minLength' => 2,
            'maxLength' => NULL,
        ],
    ],
];",
            $this->root->getChild('package.php')->getContent()
        );
    }

    public function testFindTheFirstComposerVendorFolder(): void
    {
        $command = new class() extends OptionDumpCommand {
            use NormalizePathAndDirectorySeparatorTrait;

            /**
             * {@inheritdoc}
             */
            public function getComposerVendorPath(): string
            {
                return self::normalizeDirectorySeparator(parent::getComposerVendorPath());
            }
        };

        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__, 6) . '/vendor/composer/'),
            $command->getComposerVendorPath()
        );
    }
}
