<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests\ParameterProcessor;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ParameterProcessor\EnvParameterProcessor;
use Viserio\Component\Config\Repository;

/**
 * @internal
 */
final class EnvParameterProcessorTest extends TestCase
{
    /**
     * @var \Viserio\Component\Config\Repository
     */
    private $repository;

    /**
     * @var \Viserio\Component\Config\ParameterProcessor\EnvParameterProcessor
     */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new Repository();
        $this->processor  = new EnvParameterProcessor();

        $this->repository->addParameterProcessor($this->processor);
    }

    public function testSupports(): void
    {
        static::assertTrue($this->processor->supports('%' . EnvParameterProcessor::getReferenceKeyword() . ':test%'));
        static::assertFalse($this->processor->supports('test'));
    }

    public function testGetReferenceKeyword(): void
    {
        static::assertSame('env', EnvParameterProcessor::getReferenceKeyword());
    }

    public function testProcess(): void
    {
        \putenv('LOCAL=local');

        static::assertSame('local', $this->processor->process('%env:LOCAL%'));

        $this->repository->set('foo', '%env:LOCAL%');

        static::assertSame('local', $this->repository->get('foo'));

        \putenv('LOCAL=');
        \putenv('LOCAL');
    }
}
