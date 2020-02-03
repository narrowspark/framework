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

namespace Viserio\Component\Log\Tests\Traits;

use Monolog\Logger as MonologLogger;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Log\Traits\ParseLevelTrait;
use Viserio\Contract\Log\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class ParseLevelTraitTest extends TestCase
{
    use ParseLevelTrait;

    /**
     * @dataProvider provideParseLevelCases
     *
     * @param string $stringLevel
     * @param int    $monologLevel
     */
    public function testParseLevel(string $stringLevel, int $monologLevel): void
    {
        self::assertEquals(self::parseLevel($stringLevel), $monologLevel);
    }

    public function testParseLevelToThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid log level.');

        self::parseLevel('test');
    }

    public static function provideParseLevelCases(): iterable
    {
        return [
            ['debug', MonologLogger::DEBUG],
            ['info', MonologLogger::INFO],
            ['notice', MonologLogger::NOTICE],
            ['warning', MonologLogger::WARNING],
            ['error', MonologLogger::ERROR],
            ['critical', MonologLogger::CRITICAL],
            ['alert', MonologLogger::ALERT],
            ['emergency', MonologLogger::EMERGENCY],
        ];
    }
}
