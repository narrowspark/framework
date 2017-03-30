<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests;

use PHPUnit\Framework\TestCase;
use Monolog\Logger;
use DateTimeImmutable;
use Monolog\Formatter\FormatterInterface;
use Viserio\Component\Log\Formatters\VarDumperFormatter;
use Symfony\Component\VarDumper\Cloner\VarCloner;
class VarDumperFormatterTest extends TestCase
{
    public function testFormat()
    {
        $cloner = new VarCloner();
        $formater = new VarDumperFormatter($cloner);

        self::assertEquals(
            $this->getRecord(Logger::WARNING, 'test', $cloner->cloneVar([]), $cloner->cloneVar([])),
            $formater->format($this->getRecord())
        );
    }

    public function testFormatBatch()
    {
        $cloner = new VarCloner();
        $formater = new VarDumperFormatter($cloner);

        self::assertEquals(
            $this->getMultipleRecords($cloner->cloneVar([]), $cloner->cloneVar([])),
            $formater->formatBatch($this->getMultipleRecords())
        );
    }

    /**
     * @return array Record
     */
    protected function getRecord($level = Logger::WARNING, $message = 'test', $context = [], $extra = [])
    {
        return [
            'message' => $message,
            'context' => $context,
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'channel' => 'test',
            'datetime' => new DateTimeImmutable('now'),
            'extra' => $extra,
        ];
    }

    /**
     * @return array
     */
    protected function getMultipleRecords($context = [], $extra = [])
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
