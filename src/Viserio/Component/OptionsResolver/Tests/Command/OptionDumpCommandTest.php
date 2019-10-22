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

namespace Viserio\Component\OptionsResolver\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use org\bovigo\vfs\vfsStream;
use RuntimeException;
use Viserio\Component\Console\Tester\CommandTestCase;
use Viserio\Component\OptionsResolver\Command\OptionDumpCommand;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\Options\ConfigurationFixture;
use Viserio\Component\Parser\Dumper;

/**
 * @internal
 *
 * @small
 */
final class OptionDumpCommandTest extends CommandTestCase
{
    /** @var \org\bovigo\vfs\vfsStreamDirectory */
    private $root;

    /** @var \Viserio\Component\OptionsResolver\Command\OptionDumpCommand */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup();

        $this->command = new OptionDumpCommand();
    }

    public function testCommandCantCreateDir(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config directory [vfs://bar] cannot be created or is write protected.');

        $dir = vfsStream::newDirectory('bar', 0000);

        $this->executeCommand($this->command, ['class' => ConnectionComponentConfiguration::class, 'dir' => $dir->url()], ['interactive' => false]);
    }

    public function testCommandWithMerge(): void
    {
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

        $this->executeCommand($this->command, ['class' => ConfigurationFixture::class, 'dir' => $this->root->url(), '--merge' => true], ['interactive' => false]);

        $expected = <<<'PHP'
<?php
declare(strict_types=1);

return [
    'vendor' => [
        'package' => [
            'minLength' => 2,
            'maxLength' => null,
        ],
    ],
];

PHP;

        self::assertEquals(
            $expected,
            \str_replace("\r\n", "\n", $this->root->getChild('package.php')->getContent())
        );
    }

    public function testCommandWithShow(): void
    {
        $tester = $this->executeCommand($this->command, ['class' => ConfigurationFixture::class, 'dir' => $this->root->url(), '--show' => true], ['interactive' => false]);

        $expected = <<<'PHP'
Output array:

<?php
declare(strict_types=1);

return [
    'vendor' => [
        'package' => [
            'minLength' => 2,
            'maxLength' => null,
        ],
    ],
];


PHP;

        self::assertEquals($expected, $tester->getDisplay(true));
    }

    public function testCommandShowError(): void
    {
        $tester = $this->executeCommand($this->command, ['class' => ConfigurationFixture::class, 'dir' => $this->root->url(), '--format' => 'json'], ['interactive' => false]);

        self::assertSame(
            "Only the php format is supported; use composer req viserio/parser to get [json], [xml], [yml] output.\n",
            $tester->getDisplay(true)
        );
    }

    public function testCommandWithDumper(): void
    {
        $this->application->setContainer(new ArrayContainer([Dumper::class => new Dumper()]));

        $this->executeCommand($this->command, ['class' => ConfigurationFixture::class, 'dir' => $this->root->url(), '--format' => 'json'], ['interactive' => false]);

        $expected = <<<'JSON'
{
    "vendor": {
        "package": {
            "minLength": 2,
            "maxLength": null
        }
    }
}

JSON;

        self::assertEquals(
            $expected,
            \str_replace("\r\n", "\n", $this->root->getChild('package.json')->getContent())
        );
    }
}
