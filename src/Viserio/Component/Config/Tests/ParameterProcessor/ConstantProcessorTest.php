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

namespace Viserio\Component\Config\Tests\ParameterProcessor;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ParameterProcessor\ConstantProcessor;
use Viserio\Component\Config\Repository;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\ParameterProcessor\AbstractParameterProcessor
 * @covers \Viserio\Component\Config\ParameterProcessor\ConstantProcessor
 */
final class ConstantProcessorTest extends TestCase
{
    public const CONFIG_TEST = 'config';

    /** @var \Viserio\Component\Config\Repository */
    private $repository;

    /** @var \Viserio\Component\Config\ParameterProcessor\ConstantProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new Repository();
        $this->processor = new ConstantProcessor();

        $this->repository->addParameterProcessor($this->processor);
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{' . ConstantProcessor::getReferenceKeyword() . ':TEST}'));
        self::assertFalse($this->processor->supports('test'));
        self::assertTrue($this->processor->supports('{' . ConstantProcessor::getReferenceKeyword() . ':ConfigDir}/test'));
    }

    public function testGetReferenceKeyword(): void
    {
        self::assertSame('const', ConstantProcessor::getReferenceKeyword());
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
        self::assertSame($constantValue, $this->processor->process('{' . ConstantProcessor::getReferenceKeyword() . ':' . $constantString . '}'));

        $this->repository->set('foo-dir', '{' . ConstantProcessor::getReferenceKeyword() . ':' . $constantString . '}');

        self::assertSame($constantValue, $this->repository->get('foo-dir'));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function provideProcessCases(): iterable
    {
        if (! \defined('LAMINAS_CONFIG_PROCESSOR_CONSTANT_TEST')) {
            \define('LAMINAS_CONFIG_PROCESSOR_CONSTANT_TEST', 'test-key');
        }

        //                                    constantString,                        constantValue
        return [
            'constant' => ['LAMINAS_CONFIG_PROCESSOR_CONSTANT_TEST', LAMINAS_CONFIG_PROCESSOR_CONSTANT_TEST],
            'class-constant' => [__CLASS__ . '::CONFIG_TEST',           self::CONFIG_TEST],
            'class-pseudo-constant' => [__CLASS__ . '::class',                 self::class],
            'class-pseudo-constant-upper' => [__CLASS__ . '::CLASS',                 self::class],
        ];
    }
}
