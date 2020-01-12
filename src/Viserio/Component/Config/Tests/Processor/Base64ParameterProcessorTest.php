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
use Viserio\Component\Config\Processor\Base64ParameterProcessor;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\Processor\AbstractParameterProcessor
 * @covers \Viserio\Component\Config\Processor\Base64ParameterProcessor
 */
final class Base64ParameterProcessorTest extends TestCase
{
    public const CONFIG_TEST = 'config';

    /** @var \Viserio\Component\Config\Processor\Base64ParameterProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new Base64ParameterProcessor();
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{W10=|base64}'));
        self::assertFalse($this->processor->supports('test'));
        self::assertTrue($this->processor->supports('{W10=|base64_decode}'));
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame(['base64' => 'string', 'base64_decode' => 'string'], Base64ParameterProcessor::getProvidedTypes());
    }

    /**
     * @dataProvider provideProcessCases
     *
     * @param string $value
     * @param string $parameter
     *
     * @return void
     */
    public function testProcess($value, string $parameter): void
    {
        self::assertSame($value, $this->processor->process($parameter));
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function provideProcessCases(): iterable
    {
        return [
            ['[]', 'W10=|base64'],
            ['[]', 'W10=|base64_decode'],
        ];
    }
}
