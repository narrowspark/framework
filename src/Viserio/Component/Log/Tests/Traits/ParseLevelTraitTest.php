<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Traits;

use Monolog\Logger as MonologLogger;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Log\Traits\ParseLevelTrait;

class ParseLevelTraitTest extends TestCase
{
    use ParseLevelTrait;

    /**
     * @dataProvider provideLevels
     *
     * @param string $stringLevel
     * @param string $monologLevel
     */
    public function testParseLevel(string $stringLevel, string $monologLevel): void
    {
        self::assertEquals(self::parseLevel($stringLevel), $monologLevel);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Log\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid log level.
     */
    public function testParseLevelToThrowException(): void
    {
        self::parseLevel('test');
    }

    public function provideLevels()
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
