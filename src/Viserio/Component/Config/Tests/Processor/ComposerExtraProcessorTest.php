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
use Viserio\Component\Config\Processor\ComposerExtraProcessor;
use Viserio\Contract\Config\Exception\RuntimeException;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\Processor\AbstractParameterProcessor
 * @covers \Viserio\Component\Config\Processor\ComposerExtraProcessor
 */
final class ComposerExtraProcessorTest extends TestCase
{
    /** @var \Viserio\Component\Config\Processor\ComposerExtraProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new ComposerExtraProcessor(
            \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture',
            '_composer.json'
        );
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{test|composer-extra}'));
        self::assertFalse($this->processor->supports('test'));
        self::assertTrue($this->processor->supports('{config-dir|composer-extra}/test'));
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame(['composer-extra' => 'string'], ComposerExtraProcessor::getProvidedTypes());
    }

    public function testProcess(): void
    {
        self::assertSame('config', $this->processor->process('config-dir|composer-extra'));
    }

    public function testProcessThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Syntax error in [' . \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'composer_error.json] file.');

        $processor = new ComposerExtraProcessor(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture', 'composer_error.json');
        $processor->process('{config-dir|composer-extra}');
    }
}
