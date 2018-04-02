<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Test\Configurator;


use Composer\Composer;
use Composer\IO\NullIO;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Discovery\Configurator\GitIgnoreConfigurator;
use Viserio\Component\Discovery\Package;

class GitignoreConfiguratorTest extends TestCase
{
    /**
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @var \Composer\IO\NullIo
     */
    private $nullIo;

    /**
     * @var \Viserio\Component\Discovery\Configurator\GitignoreConfigurator
     */
    private $configurator;

    /**
     * @var string
     */
    private $gitignorePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->composer = new Composer();
        $this->nullIo   = new NullIO();

        $this->configurator = new GitIgnoreConfigurator($this->composer, $this->nullIo, ['public-dir' => 'public']);

        $this->gitignorePath = sys_get_temp_dir() . '/.gitignore';

        \touch($this->gitignorePath);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        \unlink($this->gitignorePath);
    }

    public function testConfigureAndUnconfigure()
    {
        $package = new Package(
            'FooBundle',
            __DIR__,
            [
                'package_version' => '1',
                Package::CONFIGURE => [
                    'git_ignore' => [
                        '.env',
                        '/%PUBLIC_DIR%/css/',
                    ]
                ]
            ]
        );

        $gitignoreContents1 = <<<EOF
###> FooBundle ###
.env
/public/css/
###< FooBundle ###
EOF;

        $package2 = new Package(
            'BarBundle',
            __DIR__,
            [
                'package_version' => '1',
                Package::CONFIGURE => [
                    'git_ignore' => [
                        '/var/',
                        '/vendor/',
                    ]
                ]
            ]
        );

        $gitignoreContents2 = <<<EOF
###> BarBundle ###
/var/
/vendor/
###< BarBundle ###
EOF;

        $this->configurator->configure($package);

        self::assertStringEqualsFile($this->gitignorePath, "\n".$gitignoreContents1."\n");

        $this->configurator->configure($package2);

        self::assertStringEqualsFile($this->gitignorePath, "\n".$gitignoreContents1."\n\n".$gitignoreContents2."\n");

        $this->configurator->configure($package);
        $this->configurator->configure($package2);

        self::assertStringEqualsFile($this->gitignorePath, "\n".$gitignoreContents1."\n\n".$gitignoreContents2."\n");

        $this->configurator->unconfigure($package);

        self::assertStringEqualsFile($this->gitignorePath, $gitignoreContents2."\n");

        $this->configurator->unconfigure($package2);

        self::assertStringEqualsFile($this->gitignorePath, '');
    }
}
