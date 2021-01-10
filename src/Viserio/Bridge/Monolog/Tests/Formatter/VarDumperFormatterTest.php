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

namespace Viserio\Bridge\Monolog\Tests;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Viserio\Bridge\Monolog\Formatter\VarDumperFormatter;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class VarDumperFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $cloner = new VarCloner();
        $formater = new VarDumperFormatter($cloner);

        self::assertEquals(
            $this->getRecord(Logger::WARNING, 'test', $cloner->cloneVar([]), $cloner->cloneVar([])),
            $formater->format($this->getRecord())
        );
    }

    public function testFormatBatch(): void
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
    protected function getRecord($level = Logger::WARNING, $message = 'test', $context = [], $extra = []): array
    {
        return [
            'message' => $message,
            'context' => $context,
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'channel' => 'test',
            'datetime' => 'now',
            'extra' => $extra,
        ];
    }

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
