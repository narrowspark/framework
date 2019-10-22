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

namespace Viserio\Component\Container\Tests\UnitTest;

use Narrowspark\TestingHelper\Traits\AssertGetterSetterTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Pipeline\AutowirePipe;
use Viserio\Component\Container\PipelineConfig;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
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
     * @param string $setterAndGetterName
     *
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

    public function provideSetterAndGetterCases(): iterable
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
