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
use RuntimeException;
use Viserio\Component\Config\ParameterProcessor\ComposerExtraProcessor;
use Viserio\Component\Config\Repository;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\ParameterProcessor\AbstractParameterProcessor
 * @covers \Viserio\Component\Config\ParameterProcessor\ComposerExtraProcessor
 */
final class ComposerExtraProcessorTest extends TestCase
{
    /** @var \Viserio\Component\Config\Repository */
    private $repository;

    /** @var \Viserio\Component\Config\ParameterProcessor\ComposerExtraProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new Repository();
        $this->processor = new ComposerExtraProcessor(
            \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture',
            '_composer.json'
        );

        $this->repository->addParameterProcessor($this->processor);
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('%' . ComposerExtraProcessor::getReferenceKeyword() . ':test%'));
        self::assertFalse($this->processor->supports('test'));
        self::assertTrue($this->processor->supports('%' . ComposerExtraProcessor::getReferenceKeyword() . ':config-dir%/test'));
    }

    public function testGetReferenceKeyword(): void
    {
        self::assertSame('composer-extra', ComposerExtraProcessor::getReferenceKeyword());
    }

    public function testProcess(): void
    {
        self::assertSame('config', $this->processor->process('%' . ComposerExtraProcessor::getReferenceKeyword() . ':config-dir%'));
        self::assertSame('config/test', $this->processor->process('%' . ComposerExtraProcessor::getReferenceKeyword() . ':config-dir%/test'));
        self::assertSame('%' . ComposerExtraProcessor::getReferenceKeyword() . ':foo-dir%/test', $this->processor->process('%' . ComposerExtraProcessor::getReferenceKeyword() . ':foo-dir%/test'));

        $this->repository->set('foo-dir', '%' . ComposerExtraProcessor::getReferenceKeyword() . ':config-dir%');

        self::assertSame('config', $this->repository->get('foo-dir'));
    }

    public function testProcessThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Syntax error in [' . \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'composer_error.json] file.');

        $processor = new ComposerExtraProcessor(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture', 'composer_error.json');
        $processor->process('%' . ComposerExtraProcessor::getReferenceKeyword() . ':config-dir%');
    }
}
