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
        static::assertTrue($this->processor->supports('%' . ComposerExtraProcessor::getReferenceKeyword() . ':test%'));
        static::assertFalse($this->processor->supports('test'));
    }

    public function testGetReferenceKeyword(): void
    {
        static::assertSame('composer-extra', ComposerExtraProcessor::getReferenceKeyword());
    }

    public function testProcess(): void
    {
        static::assertSame('config', $this->processor->process('%composer-extra:config-dir%'));

        $this->repository->set('foo-dir', '%composer-extra:config-dir%');

        static::assertSame('config', $this->repository->get('foo-dir'));
    }

    public function testProcessThrowException(): void
    {
        $composerJsonPath = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'composer_error.json';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Syntax error in [' . $composerJsonPath . '] file.');

        $processor = new ComposerExtraProcessor($composerJsonPath);

        $processor->process('%composer-extra:config-dir%');
    }
}
