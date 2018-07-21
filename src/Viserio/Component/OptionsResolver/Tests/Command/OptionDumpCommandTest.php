<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException;
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

        static::assertEquals("Argument [dir] can't be empty.\n", $tester->getDisplay(true));
    }

    public function testCommandCantCreateDir(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Config directory [vfs://bar] cannot be created or is write protected.');

        $dir = vfsStream::newDirectory('bar', 0000);

        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $dir->url()], ['interactive' => false]);

        static::assertEquals('Argument [dir] can\'t be empty.', $tester->getDisplay(true));
    }

    public function testCommandWithMerge(): void
    {
        $tester  = new CommandTester($this->command);
        $content = <<<'PHP'
<?php
declare(strict_types=1);

return [
    'vendor' => [
        'package' => [
            'minLength' => 2,
        ],
    ],
];

PHP;

        vfsStream::newFile('package.php')
            ->withContent($content)
            ->at($this->root);

        $tester->execute(['dir' => $this->root->url(), '--merge' => true], ['interactive' => false]);

        $expected = <<<'PHP'
Searching for php classes with implemented \Viserio\Component\Contract\OptionsResolver\RequiresConfig interface.
 0/1 [>---------------------------]   0%
 1/1 [============================] 100%

PHP;

        static::assertEquals(
            \str_replace("\r\n", '', $expected),
            \str_replace("\r\n", '', $tester->getDisplay(true))
        );

        $expected = <<<'PHP'
<?php
declare(strict_types=1);

return [
    'vendor' => [
        'package' => [
            'minLength' => 2,
            'maxLength' => NULL,
        ],
    ],
];

PHP;

        static::assertEquals(
            \str_replace("\r\n", "\n", $expected),
            \str_replace("\r\n", "\n", $this->root->getChild('package.php')->getContent())
        );
    }

    public function testCommandWithShow(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $this->root->url(), '--show' => true], ['interactive' => false]);

        $expected = <<<'PHP'
Searching for php classes with implemented \Viserio\Component\Contract\OptionsResolver\RequiresConfig interface.
 0/1 [>---------------------------]   0%
 1/1 [============================] 100%
Output array:

<?php
declare(strict_types=1);

return [
    'vendor' => [
        'package' => [
            'minLength' => 2,
            'maxLength' => NULL,
        ],
    ],
];


PHP;

        static::assertEquals($expected, $tester->getDisplay(true));
    }

    public function testCommandWithDirArgument(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $this->root->url()], ['interactive' => false]);

        $expected = <<<'PHP'
<?php
declare(strict_types=1);

return [
    'vendor' => [
        'package' => [
            'minLength' => 2,
            'maxLength' => NULL,
        ],
    ],
];

PHP;

        static::assertEquals(
            \str_replace("\r\n", "\n", $expected),
            \str_replace("\r\n", "\n", $this->root->getChild('package.php')->getContent())
        );
    }

    public function testCommandShowError(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $this->root->url(), '--format' => 'json'], ['interactive' => false]);

        static::assertSame(
            "Only the php format is supported; use composer req viserio/parser to get [json], [xml], [yml] output.\n",
            $tester->getDisplay(true)
        );
    }

    public function testCommandWithDumper(): void
    {
        $container = new ArrayContainer([Dumper::class => new Dumper()]);

        $this->command->setContainer($container);

        $tester = new CommandTester($this->command);
        $tester->execute(['dir' => $this->root->url()], ['interactive' => false]);

        $expected = <<<'PHP'
<?php
declare(strict_types=1);

return [
    'vendor' => [
        'package' => [
            'minLength' => 2,
            'maxLength' => NULL,
        ],
    ],
];

PHP;

        static::assertEquals(
            \str_replace("\r\n", "\n", $expected),
            \str_replace("\r\n", "\n", $this->root->getChild('package.php')->getContent())
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

        static::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__, 6) . '/vendor/composer/'),
            $command->getComposerVendorPath()
        );
    }
}
