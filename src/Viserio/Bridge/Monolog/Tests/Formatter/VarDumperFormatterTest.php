<?php
declare(strict_types=1);
namespace Viserio\Bridge\Monolog\Tests;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Viserio\Bridge\Monolog\Formatter\VarDumperFormatter;

/**
 * @internal
 */
final class VarDumperFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $cloner   = new VarCloner();
        $formater = new VarDumperFormatter($cloner);

        $this->assertEquals(
            $this->getRecord(Logger::WARNING, 'test', $cloner->cloneVar([]), $cloner->cloneVar([])),
            $formater->format($this->getRecord())
        );
    }

    public function testFormatBatch(): void
    {
        $cloner   = new VarCloner();
        $formater = new VarDumperFormatter($cloner);

        $this->assertEquals(
            $this->getMultipleRecords($cloner->cloneVar([]), $cloner->cloneVar([])),
            $formater->formatBatch($this->getMultipleRecords())
        );
    }

    /**
     * @param mixed $level
     * @param mixed $message
     * @param mixed $context
     * @param mixed $extra
     *
     * @return array Record
     */
    protected function getRecord($level = Logger::WARNING, $message = 'test', $context = [], $extra = []): array
    {
        return [
            'message'    => $message,
            'context'    => $context,
            'level'      => $level,
            'level_name' => Logger::getLevelName($level),
            'channel'    => 'test',
            'datetime'   => 'now',
            'extra'      => $extra,
        ];
    }

    /**
     * @param mixed $context
     * @param mixed $extra
     *
     * @return array
     */
    protected function getMultipleRecords($context = [], $extra = []): array
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
