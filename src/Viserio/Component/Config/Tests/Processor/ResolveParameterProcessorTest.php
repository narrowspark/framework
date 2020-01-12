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
use Viserio\Component\Config\Processor\ResolveParameterProcessor;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\Processor\AbstractParameterProcessor
 * @covers \Viserio\Component\Config\Processor\ResolveParameterProcessor
 */
final class ResolveParameterProcessorTest extends TestCase
{
    /** @var \Viserio\Component\Config\Processor\ResolveParameterProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new ResolveParameterProcessor([
            'test' => 'value',
            'disks' => [
                'local' => [
                    'driver' => 'local',
                    'root' => 'd',
                ],
                'public' => [
                    'driver' => 'local',
                    'root' => '',
                    'url' => 'parameter',
                    'visibility' => [
                        'test' => 'parameter value',
                    ],
                ],
            ],
        ]);
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame(['resolve' => 'string'], ResolveParameterProcessor::getProvidedTypes());
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{test|resolve}'));
        self::assertFalse($this->processor->supports('test'));
    }

    public function testProcess(): void
    {
        self::assertSame('value', $this->processor->process('test|resolve'));

        // doted
        self::assertSame('local', $this->processor->process('disks.local.driver|resolve'));
    }
}
