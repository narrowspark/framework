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

namespace Viserio\Component\WebServer\Tests\Command;

use Viserio\Component\Console\Tester\CommandTestCase;
use Viserio\Component\WebServer\Command\ServerStopCommand;

/**
 * @internal
 *
 * @small
 */
final class ServerStopCommandTest extends CommandTestCase
{
    /** @var string */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = __DIR__ . \DIRECTORY_SEPARATOR . '.web-server-pid';
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        @\unlink($this->path);
    }

    public function testCommand(): void
    {
        @\file_put_contents($this->path, '127.0.0.1:8080');

        $output = $this->executeCommand(new ServerStopCommand(), ['--pidfile' => $this->path]);

        self::assertSame('[OK] Stopped the web server.', \trim($output->getDisplay(true)));
        self::assertSame(0, $output->getStatusCode());
    }

    public function testCommandToReturnError(): void
    {
        $output = $this->executeCommand(new ServerStopCommand());

        self::assertSame('No web server is listening.', \trim($output->getDisplay(true)));
        self::assertSame(1, $output->getStatusCode());
    }
}
