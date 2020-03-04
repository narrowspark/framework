<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\WebServer\Event;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class DumpListenerEventTest extends TestCase
{
    public function testConfigure(): void
    {
        $prevDumper = VarDumper::setHandler('var_dump');

        VarDumper::setHandler($prevDumper);

        $cloner = new MockCloner();
        $dumper = new MockDumper();

        \ob_start();

        $exception = null;

        $listener = new DumpListenerEvent($cloner, $dumper);

        try {
            $listener->configure();

            VarDumper::dump('foo');
            VarDumper::dump('bar');

            self::assertSame('+foo-+bar-', \ob_get_clean());
        } catch (Exception $exception) {
        }

        VarDumper::setHandler($prevDumper);

        if ($exception !== null) {
            throw $exception;
        }
    }
}

class MockCloner implements ClonerInterface
{
    public function cloneVar($var): Data
    {
        return new Data([[$var . '-']]);
    }
}

class MockDumper implements DataDumperInterface
{
    public function dump(Data $data): void
    {
        echo '+' . $data->getValue();
    }
}
