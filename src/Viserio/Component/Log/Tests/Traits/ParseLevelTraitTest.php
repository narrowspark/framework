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

namespace Viserio\Component\Log\Tests\Traits;

use Monolog\Logger as MonologLogger;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Log\Traits\ParseLevelTrait;
use Viserio\Contract\Log\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ParseLevelTraitTest extends TestCase
{
    use ParseLevelTrait;

    /**
     * @dataProvider provideParseLevelCases
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
