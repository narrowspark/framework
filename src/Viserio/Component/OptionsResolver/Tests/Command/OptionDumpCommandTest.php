<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\OptionsResolver\Command\OptionDumpCommand;
use Viserio\Component\Parser\Dumper;

class OptionDumpCommandTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testCommand(): void
    {
        $command = new OptionDumpCommand();

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

    public function testCommandShowError(): void
    {
        $command = new OptionDumpCommand();

        $tester = new CommandTester($command);
        $tester->execute(['dir' => $this->root->url(), '--format' => 'json'], ['interactive' => false]);

        $output = $tester->getDisplay(true);

        self::assertSame("Only the php format is supported; use composer req viserio/parsers to get json, xml, yml output.\n", $output);
    }

    public function testCommandWithDumper(): void
    {
        $container = new ArrayContainer([Dumper::class => new Dumper()]);
        $command   = new OptionDumpCommand();
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
