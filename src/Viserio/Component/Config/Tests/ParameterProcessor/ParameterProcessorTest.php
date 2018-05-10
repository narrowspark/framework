<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests\ParameterProcessor;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ParameterProcessor\ParameterProcessor;
use Viserio\Component\Config\Repository;

class ParameterProcessorTest extends TestCase
{
    /**
     * @var \Viserio\Component\Config\Repository
     */
    private $repository;

    /**
     * @var \Viserio\Component\Config\ParameterProcessor\ParameterProcessor
     */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new Repository();

        $this->processor = new ParameterProcessor([
            'test'  => 'value',
            'disks' => [
                'local' => [
                    'driver' => 'local',
                    'root'   => 'd',
                ],
                'public' => [
                    'driver'     => 'local',
                    'root'       => '',
                    'url'        => 'parameter',
                    'visibility' => [
                        'test' => 'parameter value',
                    ],
                ],
            ],
        ]);

        $this->repository->addParameterProcessor($this->processor);
    }

    public function testGetReferenceKeyword(): void
    {
        self::assertSame('parameter', $this->processor->getReferenceKeyword());
    }

    public function testProcess(): void
    {
        self::assertSame('value', $this->processor->process('%parameter:test%'));
        // doted
        self::assertSame('local', $this->processor->process('%parameter:disks.local.driver%'));
    }
}
