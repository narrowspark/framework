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

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\OptionsResolver\Command\OptionDumpCommand
     */
    private $command;

    public function setUp(): void
    {
        $this->root = vfsStream::setup();
        $this->command = new class extends OptionDumpCommand {
            use NormalizePathAndDirectorySeparatorTrait;

            /**
             * {@inheritdoc}
             */
            protected function getComposerVendorPath(): string
            {
                return self::normalizeDirectorySeparator(dirname(__DIR__) . '/Fixtures/composer');
            }
        };
    }

    public function testCommand(): void
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

        self::assertSame("Only the php format is supported; use composer req viserio/parser to get json, xml, yml output.\n", $output);
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
}
