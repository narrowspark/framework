<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests\ParameterProcessor;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ParameterProcessor\ComposerExtraProcessor;
use Viserio\Component\Config\Repository;

/**
 * @internal
 */
final class ComposerExtraProcessorTest extends TestCase
{
    /**
     * @var \Viserio\Component\Config\Repository
     */
    private $repository;

    /**
     * @var \Viserio\Component\Config\ParameterProcessor\ComposerExtraProcessor
     */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new Repository();
        $this->processor  = new ComposerExtraProcessor(
            \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'composer.json'
        );

        $this->repository->addParameterProcessor($this->processor);
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->processor->supports('%' . ComposerExtraProcessor::getReferenceKeyword() . ':test%'));
        $this->assertFalse($this->processor->supports('test'));
        $this->assertTrue($this->processor->supports('%' . ComposerExtraProcessor::getReferenceKeyword() . ':config-dir%/test'));
    }

    public function testGetReferenceKeyword(): void
    {
        $this->assertSame('composer-extra', ComposerExtraProcessor::getReferenceKeyword());
    }

    public function testProcess(): void
    {
        $this->assertSame('config', $this->processor->process('%' . ComposerExtraProcessor::getReferenceKeyword() . ':config-dir%'));
        $this->assertSame('config/test', $this->processor->process('%' . ComposerExtraProcessor::getReferenceKeyword() . ':config-dir%/test'));

        $this->repository->set('foo-dir', '%' . ComposerExtraProcessor::getReferenceKeyword() . ':config-dir%');

        $this->assertSame('config', $this->repository->get('foo-dir'));
    }

    public function testProcessThrowException(): void
    {
        $composerJsonPath = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'composer_error.json';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Syntax error in [' . $composerJsonPath . '] file.');

        $processor = new ComposerExtraProcessor($composerJsonPath);

        $processor->process('%' . ComposerExtraProcessor::getReferenceKeyword() . ':config-dir%');
    }
}
