<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Automatic;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Console\Automatic\CerebroScriptExtender;

/**
 * @internal
 */
final class CerebroScriptExtenderTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Console\Automatic\CerebroScriptExtender
     */
    private $extender;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->extender = new CerebroScriptExtender(new Composer(), new NullIO(), []);
    }

    public function testGetType(): void
    {
        static::assertSame('cerebro-cmd', CerebroScriptExtender::getType());
    }

    public function testExpand(): void
    {
        $output = $this->extender->expand('echo "hallo";');

        static::assertContains('php', $output);
        static::assertContains('php.ini', $output);
        static::assertContains('cerebro echo "hallo";', $output);
    }

    public function testExpandWithIniLoad(): void
    {
        // clear the composer env
        \putenv('COMPOSER_ORIGINAL_INIS=');
        \putenv('COMPOSER_ORIGINAL_INIS');

        $output = $this->extender->expand('echo "hallo";');

        static::assertContains('php', $output);
        static::assertContains('php.ini', $output);
        static::assertContains('cerebro echo "hallo";', $output);
    }

    public function testExpandWithAnsi(): void
    {
        // clear the composer env
        \putenv('COMPOSER_ORIGINAL_INIS=');
        \putenv('COMPOSER_ORIGINAL_INIS');

        $ioMock = $this->mock(IOInterface::class);
        $ioMock->shouldReceive('isDecorated')
            ->once()
            ->andReturn(true);

        $extender = new CerebroScriptExtender(new Composer(), $ioMock, []);

        $output = $extender->expand('echo "hallo";');

        static::assertContains('php', $output);
        static::assertContains('php.ini', $output);
        static::assertContains('cerebro --ansi echo "hallo";', $output);
    }
}
