<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests\ParameterProcessor;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ParameterProcessor\ParameterProcessor;
use Viserio\Component\Config\Repository;

/**
 * @internal
 */
final class ParameterProcessorTest extends TestCase
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

    public function testSupports(): void
    {
        static::assertTrue($this->processor->supports('%' . ParameterProcessor::getReferenceKeyword() . ':test%'));
        static::assertFalse($this->processor->supports('test'));
    }

    public function testGetReferenceKeyword(): void
    {
        static::assertSame('parameter', ParameterProcessor::getReferenceKeyword());
    }

    public function testProcess(): void
    {
        static::assertSame('value', $this->processor->process('%parameter:test%'));

        $this->repository->set('bar', '%parameter:test%');

        static::assertSame('value', $this->repository->get('bar'));

        // doted
        static::assertSame('local', $this->processor->process('%parameter:disks.local.driver%'));
    }
}
