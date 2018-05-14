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
use Viserio\Component\Config\ParameterProcessor\ParameterProcessor;
use Viserio\Component\Config\Repository;

/**
 * @internal
 *
 * @small
 */
final class ParameterProcessorTest extends TestCase
{
    /** @var \Viserio\Component\Config\Repository */
    private $repository;

    /** @var \Viserio\Component\Config\ParameterProcessor\ParameterProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new Repository();

        $this->processor = new ParameterProcessor([
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

        $this->repository->addParameterProcessor($this->processor);
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('%' . ParameterProcessor::getReferenceKeyword() . ':test%'));
        self::assertFalse($this->processor->supports('test'));
    }

    public function testGetReferenceKeyword(): void
    {
        self::assertSame('parameter', ParameterProcessor::getReferenceKeyword());
    }

    public function testProcess(): void
    {
        self::assertSame('value', $this->processor->process('%parameter:test%'));
        self::assertSame('value/go', $this->processor->process('%parameter:test%/go'));

        $this->repository->set('bar', '%parameter:test%');

        self::assertSame('value', $this->repository->get('bar'));

        // doted
        self::assertSame('local', $this->processor->process('%parameter:disks.local.driver%'));
    }
}
