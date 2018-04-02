<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Test\Configurator;

use Composer\Composer;
use Composer\IO\NullIO;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Discovery\Configurator\EnvConfigurator;
use Viserio\Component\Discovery\Package;

class EnvConfiguratorTest extends MockeryTestCase
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
     * @var \Viserio\Component\Discovery\Configurator\EnvConfigurator
     */
    private $configurator;

    /**
     * @var string
     */
    private $envDistPath;

    /**
     * @var string
     */
    private $envPath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->composer = new Composer();
        $this->nullIo   = new NullIO();

        $this->configurator = new EnvConfigurator($this->composer, $this->nullIo, []);

        $this->envDistPath = \sys_get_temp_dir() . '/.env.dist';
        $this->envPath     = \sys_get_temp_dir() . '/.env';

        \touch($this->envDistPath);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        \unlink($this->envDistPath);
        \unlink($this->envPath);
    }

    public function testConfigure(): void
    {
        $package = new Package(
            'TEST PACKAGE',
            __DIR__,
            [
                'package_version'  => '1',
                Package::CONFIGURE => [
                    'env' => [
                        'APP_ENV'         => 'test bar',
                        'APP_DEBUG'       => '0',
                        'APP_PARAGRAPH'   => "foo\n\"bar\"\\t",
                        'DATABASE_URL'    => 'mysql://root@127.0.0.1:3306/narrowspark?charset=utf8mb4&serverVersion=5.7',
                        'MAILER_URL'      => 'null://localhost',
                        'MAILER_USER'     => 'narrow',
                        '#1'              => 'Comment 1',
                        '#2'              => 'Comment 3',
                        '#TRUSTED_SECRET' => 's3cretf0rt3st"<>',
                        'APP_SECRET'      => 's3cretf0rt3st"<>',
                    ],
                ],
            ]
        );

        $this->configurator->configure($package);

        $envContents = <<<EOF
###> TEST PACKAGE ###
APP_ENV="test bar"
APP_DEBUG=0
APP_PARAGRAPH="foo\\n\\"bar\\"\\\\t"
DATABASE_URL="mysql://root@127.0.0.1:3306/narrowspark?charset=utf8mb4&serverVersion=5.7"
MAILER_URL=null://localhost
MAILER_USER=narrow
# Comment 1
# Comment 3
#TRUSTED_SECRET="s3cretf0rt3st\"<>"
APP_SECRET="s3cretf0rt3st\"<>"
###< TEST PACKAGE ###

EOF;

        self::assertStringEqualsFile($this->envDistPath, $envContents);
        self::assertStringEqualsFile($this->envPath, $envContents);
    }

    public function testUnconfigure(): void
    {
        $envConfig = [
            'APP_ENV'         => 'test',
            'APP_DEBUG'       => '0',
            '#1'              => 'Comment 1',
            '#2'              => 'Comment 3',
            '#TRUSTED_SECRET' => 's3cretf0rt3st',
            'APP_SECRET'      => 's3cretf0rt3st',
        ];

        $package = new Package(
            'env2',
            __DIR__,
            [
                'package_version'  => '1',
                Package::CONFIGURE => [
                    'env' => $envConfig,
                ],
            ]
        );

        $this->configurator->configure($package);

        $envContents = <<<'EOF'
###> env2 ###
APP_ENV=test
APP_DEBUG=0
# Comment 1
# Comment 3
#TRUSTED_SECRET=s3cretf0rt3st
APP_SECRET=s3cretf0rt3st
###< env2 ###

EOF;
        self::assertStringEqualsFile($this->envDistPath, $envContents);
        self::assertStringEqualsFile($this->envPath, $envContents);

        $package = new Package(
            'env2',
            __DIR__,
            [
                'package_version'    => '1',
                Package::UNCONFIGURE => [
                    'env' => $envConfig,
                ],
            ]
        );

        $this->configurator->unconfigure($package);

        self::assertStringEqualsFile(
            $this->envDistPath,
            <<<'EOF'

EOF
        );
        self::assertStringEqualsFile(
            $this->envPath,
            <<<'EOF'

EOF
        );
    }
}
