<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Traits;

use Monolog\Logger as MonologLogger;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Log\Traits\ParseLevelTrait;

/**
 * @internal
 */
final class ParseLevelTraitTest extends TestCase
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
        static::assertEquals(self::parseLevel($stringLevel), $monologLevel);
    }

    public function testParseLevelToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Log\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid log level.');

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
