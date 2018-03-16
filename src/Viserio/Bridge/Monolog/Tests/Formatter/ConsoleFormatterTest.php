<?php
declare(strict_types=1);
namespace Viserio\Bridge\Monolog\Tests;

use DateTime;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Monolog\Formatter\ConsoleFormatter;

class ConsoleFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $formater = new ConsoleFormatter(['colors' => false]);

        self::assertEquals(
            "16:21:54 <fg=cyan>WARNING  </> <comment>[test]</> {\"foo\":\"bar\"} [] []\n",
            $formater->format($this->getRecord(Logger::WARNING, \json_encode(['foo' => 'bar'])))
        );
    }

    public function testFormatBatch(): void
    {
        $formater = new ConsoleFormatter(['colors' => false]);

        self::assertEquals(
            [
                "16:21:54 <fg=white>DEBUG    </> <comment>[test]</> debug message 1 [] []\n",
                "16:21:54 <fg=white>DEBUG    </> <comment>[test]</> debug message 2 [] []\n",
                "16:21:54 <fg=green>INFO     </> <comment>[test]</> information [] []\n",
                "16:21:54 <fg=cyan>WARNING  </> <comment>[test]</> warning [] []\n",
                "16:21:54 <fg=yellow>ERROR    </> <comment>[test]</> error [] []\n",
            ],
            $formater->formatBatch($this->getMultipleRecords())
        );
    }

    /**
     * @param mixed $level
     * @param mixed $message
     * @param array $context
     * @param array $extra
     *
     * @return array Record
     */
    protected function getRecord($level = Logger::WARNING, $message = 'test', array $context = [], array $extra = []): array
    {
        return [
            'message'    => $message,
            'context'    => $context,
            'level'      => $level,
            'level_name' => Logger::getLevelName($level),
            'channel'    => 'test',
            'datetime'   => new DateTime('2013-05-29 16:21:54'),
            'extra'      => $extra,
        ];
    }

    /**
     * @param array $context
     * @param array $extra
     *
     * @return array
     */
    protected function getMultipleRecords(array $context = [], array $extra = []): array
    {
        return [
            $this->getRecord(Logger::DEBUG, 'debug message 1', $context, $extra),
            $this->getRecord(Logger::DEBUG, 'debug message 2', $context, $extra),
            $this->getRecord(Logger::INFO, 'information', $context, $extra),
            $this->getRecord(Logger::WARNING, 'warning', $context, $extra),
            $this->getRecord(Logger::ERROR, 'error', $context, $extra),
        ];
    }
}
