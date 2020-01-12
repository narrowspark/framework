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

namespace Viserio\Component\Config\Tests\Processor;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Processor\ConstantProcessor;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\Processor\AbstractParameterProcessor
 * @covers \Viserio\Component\Config\Processor\ConstantProcessor
 */
final class ConstantProcessorTest extends TestCase
{
    public const CONFIG_TEST = 'config';

    /** @var \Viserio\Component\Config\Processor\ConstantProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new ConstantProcessor();
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{TEST|const}'));
        self::assertFalse($this->processor->supports('test'));
        self::assertTrue($this->processor->supports('{ConfigDir|const}/test'));
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame(['const' => 'bool|int|float|string|array'], ConstantProcessor::getProvidedTypes());
    }

    /**
     * @dataProvider provideProcessCases
     *
     * @param string $constantString
     * @param mixed  $constantValue
     *
     * @return void
     */
    public function testProcess(string $constantString, $constantValue): void
    {
        self::assertSame($constantValue, $this->processor->process($constantString . '|const'));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function provideProcessCases(): iterable
    {
        if (! \defined('CONFIG_PROCESSOR_CONSTANT_TEST')) {
            \define('CONFIG_PROCESSOR_CONSTANT_TEST', 'test-key');
        }

        return [
            'constant' => ['CONFIG_PROCESSOR_CONSTANT_TEST', CONFIG_PROCESSOR_CONSTANT_TEST],
            'class-constant' => [__CLASS__ . '::CONFIG_TEST', self::CONFIG_TEST],
            'class-pseudo-constant' => [__CLASS__ . '::class', self::class],
            'class-pseudo-constant-upper' => [__CLASS__ . '::CLASS', self::class],
        ];
    }
}
