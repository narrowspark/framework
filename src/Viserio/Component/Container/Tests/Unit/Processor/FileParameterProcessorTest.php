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

namespace Viserio\Component\Container\Tests\Processor;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Processor\FileParameterProcessor;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Processor\AbstractParameterProcessor
 * @covers \Viserio\Component\Container\Processor\FileParameterProcessor
 *
 * @small
 */
final class FileParameterProcessorTest extends TestCase
{
    /** @var \Viserio\Component\Container\Processor\FileParameterProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new FileParameterProcessor();
    }

    public function testSupports(): void
    {
        self::assertTrue($this->processor->supports('{TEST|file}'));
        self::assertFalse($this->processor->supports('test'));
    }

    public function testGetProvidedTypes(): void
    {
        self::assertSame(['file' => 'string', 'require' => 'bool|int|float|string|array'], FileParameterProcessor::getProvidedTypes());
    }

    /**
     * @dataProvider provideProcessCases
     */
    public function testProcess(string $parameter, $value): void
    {
        self::assertSame($value, $this->processor->process($parameter));
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function provideProcessCases(): iterable
    {
        $path = dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'return_foo_string.php';

        return [
            [$path . '|require', 'foo'],
            [$path . '|file', "<?php\n    return 'foo';\n"],
        ];
    }

    /**
     * @dataProvider provideProcessWithMissingFileCases
     */
    public function testProcessWithMissingFile(string $parameter): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('File [return_foo_string] not found (resolved from [%s]).', $parameter));

        $this->processor->process($parameter);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function provideProcessWithMissingFileCases(): iterable
    {
        $path = 'return_foo_string';

        return [
            [$path . '|require'],
            [$path . '|file'],
        ];
    }

    public function testProcessWithInvalidProcessor(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported processor [foo] for [foo|foo] given.');

        $this->processor->process('foo|foo');
    }
}
