<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Provider\Framework\Tests\Container\Processor;

use PHPUnit\Framework\TestCase;
use Viserio\Contract\Container\Exception\RuntimeException;
use Viserio\Provider\Framework\Container\Processor\ComposerExtraProcessor;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Container\Processor\AbstractParameterProcessor
 * @covers \Viserio\Provider\Framework\Container\Processor\ComposerExtraProcessor
 */
final class ComposerExtraProcessorTest extends TestCase
{
    private ComposerExtraProcessor $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new ComposerExtraProcessor(
            \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Fixture',
            '_composer.json'
        );
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{test|composer-extra}'));
        self::assertFalse($this->processor->supports('test'));
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
        $this->expectExceptionMessage('Syntax error in [' . \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'composer_error.json] file.');

        $processor = new ComposerExtraProcessor(\dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Fixture', 'composer_error.json');
        $processor->process('{config-dir|composer-extra}');
    }
}
