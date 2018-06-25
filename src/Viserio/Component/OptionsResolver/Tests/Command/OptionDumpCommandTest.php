<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\OptionsResolver\Command\OptionDumpCommand;
use Viserio\Component\Parser\Dumper;
use Viserio\Component\Support\Invoker;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class OptionDumpCommandTest extends TestCase
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
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
        $command    = new class() extends OptionDumpCommand {
            use NormalizePathAndDirectorySeparatorTrait;

            /**
             * {@inheritdoc}
             */
            protected function getComposerVendorPath(): string
            {
                return self::normalizeDirectorySeparator(\dirname(__DIR__) . '/Fixture/composer');
            }
        };
        $command->setInvoker(new Invoker());

        $this->command = $command;
    }

    public function testCommandWithNoDirArgument(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute([], ['interactive' => false]);

        $this->assertEquals(
            'Argument [dir] can\'t be empty.',
            \trim($tester->getDisplay())
        );
    }

    public function testCommandCantCreateDir(): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Config directory [vfs://bar] cannot be created or is write protected.');

        $dir = vfsStream::newDirectory('bar', 0000);

        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $dir->url()], ['interactive' => false]);

        $this->assertEquals(
            'Argument [dir] can\'t be empty.',
            \trim($tester->getDisplay())
        );
    }

    public function testCommandWithMerge(): void
    {
        $tester = new CommandTester($this->command);

        vfsStream::newFile('package.php')
            ->withContent('<?php' . \PHP_EOL . 'declare(strict_types=1);' . \PHP_EOL . \PHP_EOL . 'return [' . \PHP_EOL . '    \'vendor\' => [' . \PHP_EOL . '        \'package\' => [' . \PHP_EOL . '            \'minLength\' => 2,' . \PHP_EOL . '        ],' . \PHP_EOL . '    ],' . \PHP_EOL . '];' . \PHP_EOL)
            ->at($this->root);

        $tester->execute(['dir' => $this->root->url(), '--merge' => true], ['interactive' => false]);

        $this->assertEquals(
            'Searching for php classes with implemented \Viserio\Component\Contract\OptionsResolver\RequiresConfig interface.' . \PHP_EOL . ' 0/1 [>---------------------------]   0%' . \PHP_EOL . ' 1/1 [============================] 100%',
            \trim($tester->getDisplay())
        );
        $this->assertEquals(
            '<?php' . \PHP_EOL . 'declare(strict_types=1);' . \PHP_EOL . \PHP_EOL . 'return [' . \PHP_EOL . '    \'vendor\' => [' . \PHP_EOL . '        \'package\' => [' . \PHP_EOL . '            \'minLength\' => 2,' . \PHP_EOL . '            \'maxLength\' => NULL,' . \PHP_EOL . '        ],' . \PHP_EOL . '    ],' . \PHP_EOL . '];' . \PHP_EOL,
            $this->root->getChild('package.php')->getContent()
        );
    }

    public function testCommandWithShow(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $this->root->url(), '--show' => true], ['interactive' => false]);

        $this->assertEquals(
            'Searching for php classes with implemented \Viserio\Component\Contract\OptionsResolver\RequiresConfig interface.' . \PHP_EOL . ' 0/1 [>---------------------------]   0%' . \PHP_EOL . ' 1/1 [============================] 100%' . \PHP_EOL . 'Output array:' . \PHP_EOL . \PHP_EOL . '<?php' . \PHP_EOL . 'declare(strict_types=1);' . \PHP_EOL . \PHP_EOL . 'return [' . \PHP_EOL . '    \'vendor\' => [' . \PHP_EOL . '        \'package\' => [' . \PHP_EOL . '            \'minLength\' => 2,' . \PHP_EOL . '            \'maxLength\' => NULL,' . \PHP_EOL . '        ],' . \PHP_EOL . '    ],' . \PHP_EOL . '];',
            \trim($tester->getDisplay())
        );
    }

    public function testCommandWithDirArgument(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $this->root->url()], ['interactive' => false]);
        $tester->getDisplay();

        $this->assertEquals(
            '<?php' . \PHP_EOL . 'declare(strict_types=1);' . \PHP_EOL . \PHP_EOL . 'return [' . \PHP_EOL . '    \'vendor\' => [' . \PHP_EOL . '        \'package\' => [' . \PHP_EOL . '            \'minLength\' => 2,' . \PHP_EOL . '            \'maxLength\' => NULL,' . \PHP_EOL . '        ],' . \PHP_EOL . '    ],' . \PHP_EOL . '];' . \PHP_EOL,
            $this->root->getChild('package.php')->getContent()
        );
    }

    public function testCommandShowError(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $this->root->url(), '--format' => 'json'], ['interactive' => false]);

        $output = $tester->getDisplay(true);

        $this->assertSame("Only the php format is supported; use composer req viserio/parser to get [json], [xml], [yml] output.\n", $output);
    }

    public function testCommandWithDumper(): void
    {
        $container = new ArrayContainer([Dumper::class => new Dumper()]);

        $this->command->setContainer($container);

        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $this->root->url()], ['interactive' => false]);
        $tester->getDisplay();

        $this->assertEquals(
            '<?php' . \PHP_EOL . 'declare(strict_types=1);' . \PHP_EOL . \PHP_EOL . 'return [' . \PHP_EOL . '    \'vendor\' => [' . \PHP_EOL . '        \'package\' => [' . \PHP_EOL . '            \'minLength\' => 2,' . \PHP_EOL . '            \'maxLength\' => NULL,' . \PHP_EOL . '        ],' . \PHP_EOL . '    ],' . \PHP_EOL . '];' . \PHP_EOL,
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

        $this->assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__, 6) . '/vendor/composer/'),
            $command->getComposerVendorPath()
        );
    }
}
