<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Config\Processor;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Config\Processor\DirectoryProcessor;
use Viserio\Component\Foundation\Console\Kernel;

/**
 * @internal
 */
final class DirectoryProcessorTest extends MockeryTestCase
{
    /**
     * Container instance.
     *
     * @var \Mockery\MockInterface|\Psr\Container\ContainerInterface
     */
    protected $containerMock;

    /**
     * @var \Viserio\Component\Foundation\Config\Processor\DirectoryProcessor
     */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->containerMock = $this->mock(ContainerInterface::class);
        $this->processor     = new DirectoryProcessor(['viserio' => ['config' => ['processor' => [DirectoryProcessor::getReferenceKeyword() => ['mapper' => ['config' => [AbstractKernel::class, 'getConfigPath']]]]]]], $this->containerMock);
    }

    public function testGetReferenceKeyword(): void
    {
        $this->assertSame('directory', DirectoryProcessor::getReferenceKeyword());
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->processor->supports('%' . DirectoryProcessor::getReferenceKeyword() . ':test%'));
        $this->assertFalse($this->processor->supports('test'));
        $this->assertTrue($this->processor->supports('%' . DirectoryProcessor::getReferenceKeyword() . ':config-dir%/test'));
    }

    public function testProcess(): void
    {
        $kernel = new Kernel();

        $this->containerMock->shouldReceive('has')
            ->twice()
            ->with(AbstractKernel::class)
            ->andReturn(true);
        $this->containerMock->shouldReceive('get')
            ->twice()
            ->andReturn($kernel);

        $this->assertSame($kernel->getConfigPath(), $this->processor->process('%' . DirectoryProcessor::getReferenceKeyword() . ':config%'));
        $this->assertSame($kernel->getConfigPath('test'), $this->processor->process('%' . DirectoryProcessor::getReferenceKeyword() . ':config%' . \DIRECTORY_SEPARATOR . 'test'));
        $this->assertSame('%' . DirectoryProcessor::getReferenceKeyword() . ':test%', $this->processor->process('%' . DirectoryProcessor::getReferenceKeyword() . ':test%'));
    }
}
