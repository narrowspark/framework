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

namespace Viserio\Component\Console\Tests\Automatic;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Console\Automatic\CerebroScriptExtender;

/**
 * @internal
 *
 * @small
 */
final class CerebroScriptExtenderTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Console\Automatic\CerebroScriptExtender */
    private $extender;

    /** @var string */
    private $binCommand;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->extender = new CerebroScriptExtender(new Composer(), new NullIO(), []);
        $this->binCommand = \defined('CEREBRO_BINARY') ? '\'cerebro\'' : 'cerebro';
    }

    public function testGetType(): void
    {
        self::assertSame('cerebro-cmd', CerebroScriptExtender::getType());
    }

    public function testExpand(): void
    {
        $output = $this->extender->expand('echo "hallo";');

        self::assertStringContainsString('php', $output);
        self::assertStringContainsString('php.ini', $output);
        self::assertStringContainsString($this->binCommand . ' echo "hallo";', $output);
    }

    public function testExpandWithIniLoad(): void
    {
        // clear the composer env
        \putenv('COMPOSER_ORIGINAL_INIS=');
        \putenv('COMPOSER_ORIGINAL_INIS');

        $output = $this->extender->expand('echo "hallo";');

        self::assertStringContainsString('php', $output);
        self::assertStringContainsString('php.ini', $output);
        self::assertStringContainsString($this->binCommand . ' echo "hallo";', $output);
    }

    public function testExpandWithAnsi(): void
    {
        // clear the composer env
        \putenv('COMPOSER_ORIGINAL_INIS=');
        \putenv('COMPOSER_ORIGINAL_INIS');

        $ioMock = \Mockery::mock(IOInterface::class);
        $ioMock->shouldReceive('isDecorated')
            ->once()
            ->andReturn(true);

        $extender = new CerebroScriptExtender(new Composer(), $ioMock, []);

        $output = $extender->expand('echo "hallo";');

        self::assertStringContainsString('php', $output);
        self::assertStringContainsString('php.ini', $output);
        self::assertStringContainsString($this->binCommand . ' --ansi echo "hallo";', $output);
    }
}
