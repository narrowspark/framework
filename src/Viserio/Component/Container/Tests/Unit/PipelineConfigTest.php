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

namespace Viserio\Component\Container\Tests\Unit;

use Narrowspark\TestingHelper\Traits\AssertGetterSetterTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Pipeline\AutowirePipe;
use Viserio\Component\Container\PipelineConfig;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\PipelineConfig
 *
 * @small
 */
final class PipelineConfigTest extends TestCase
{
    use AssertGetterSetterTrait;

    /** @var \Viserio\Component\Container\PipelineConfig */
    private $pipeline;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pipeline = new PipelineConfig();
    }

    /**
     * @dataProvider provideSetterAndGetterCases
     */
    public function testSetterAndGetter(string $setterAndGetterName): void
    {
        self::assertGetterSetter(
            $this->pipeline,
            'get' . $setterAndGetterName,
            $this->pipeline->{'get' . $setterAndGetterName}(),
            'set' . $setterAndGetterName,
            [],
            false
        );
    }

    public static function provideSetterAndGetterCases(): iterable
    {
        return [
            [
                'AfterRemovingPipelines',
            ],
            [
                'BeforeOptimizationPipelines',
            ],
            [
                'BeforeRemovingPipelines',
            ],
            [
                'OptimizationPipelines',
            ],
            [
                'RemovingPipelines',
            ],
        ];
    }

    public function testAddPipeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $type = 'foo';

        $this->expectExceptionMessage(\sprintf('Invalid type [%s].', $type));

        $this->pipeline->addPipe(new AutowirePipe(), $type);
    }
}
