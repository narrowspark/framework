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

namespace Viserio\Component\Http\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Tests\Constraint\HttpProtocolVersion;
use Viserio\Component\Http\Tests\Constraint\Immutable;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

/**
 * @internal
 */
abstract class AbstractMessageTest extends MockeryTestCase
{
    /** @var \Psr\Http\Message\MessageInterface */
    public $classToTest;

    // Test methods for default/empty instances
    public function testMessageImplementsInterface(): void
    {
        self::assertInstanceOf(MessageInterface::class, $this->classToTest);
    }

    public function testValidDefaultProtocolVersion(): void
    {
        $message = $this->classToTest;
        $version = $message->getProtocolVersion();

        self::assertIsString($version, 'getProtocolVersion must return a string');

        $message = $message->withProtocolVersion('1.0');

        self::assertEquals('1.0', $message->getProtocolVersion());

        HttpProtocolVersion::assertValid($version);
    }

    public function testValidDefaultHeaders(): void
    {
        $message = $this->classToTest;
        $headers = $message->getHeaders();

        self::assertIsArray($headers, "getHeaders an associative array of the message's headers");

        foreach ($headers as $name => $values) {
            self::assertIsString($name, 'Each key MUST be a header name');
            $this->assertValidHeaderValue($values);
        }
    }

    public function testGetHeader(): void
    {
        $message = $this->classToTest;
        $message = $message->withAddedHeader('content-type', 'text/html');
        $message = $message->withAddedHeader('content-type', 'text/plain');

        self::assertCount(2, $message->getHeader('content-type'));
        self::assertCount(2, $message->getHeader('Content-Type'));
        self::assertCount(2, $message->getHeader('CONTENT-TYPE'));

        $emptyHeader = $message->getHeader('Bar');

        self::assertCount(0, $emptyHeader);
        self::assertIsArray($emptyHeader);
    }

    public function testValidNonExistHeader(): void
    {
        $message = $this->classToTest;
        $values = $message->getHeader('not exist');

        $this->assertValidHeaderValue($values);
    }

    public function testValidNonExistHeaderLine(): void
    {
        $message = $this->classToTest;
        $headerLine = $message->getHeaderLine('not exist');

        self::assertIsString($headerLine, 'getHeaderLine must return a string');
        self::assertEmpty(
            $headerLine,
            'If the header does not appear in the message, this method MUST return an empty string'
        );
    }

    public function testValidDefaultBody(): void
    {
        $message = $this->classToTest;
        $body = $message->getBody();

        self::assertInstanceOf(
            StreamInterface::class,
            $body,
            'getBody must return instance of Psr\Http\Message\StreamInterface'
        );
    }

    /**
     * @dataProvider provideValidWithProtocolVersionCases
     *
     * @param string $expectedVersion
     */
    public function testValidWithProtocolVersion($expectedVersion): void
    {
        $message = $this->classToTest;
        $messageClone = clone $message;
        $newMessage = $message->withProtocolVersion($expectedVersion);

        self::assertEquals(
            '1.1',
            $messageClone->getProtocolVersion(),
            'getProtocolVersion does not match version set in withProtocolVersion'
        );

        self::assertEquals(
            $expectedVersion,
            $newMessage->getProtocolVersion(),
            'getProtocolVersion does not match version set in withProtocolVersion'
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function provideValidWithProtocolVersionCases(): iterable
    {
        return [
            // Description => [version],
            '1.0' => ['1.0'],
            '1.1' => ['1.1'],
            '2.0' => ['2.0'],
        ];
    }

    public function testInvalidEmptyProtocolVersion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('HTTP protocol version can not be empty.');

        $this->classToTest->withProtocolVersion('');
    }

    /**
     * @dataProvider provideValidHeaderCases
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     * @param string[]        $expectedHeaderValue
     */
    public function testValidWithHeader($headerName, $headerValue, $expectedHeaderValue): void
    {
        $message = $this->classToTest;
        $messageClone = clone $message;

        $newMessage = $message->withHeader($headerName, $headerValue);

        $this->assertImmutable($messageClone, $message, $newMessage);
        self::assertEquals(
            $expectedHeaderValue,
            $newMessage->getHeader($headerName),
            'getHeader does not match header set in withHeader'
        );
    }

    /**
     * @dataProvider provideValidHeaderCases
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     * @param string[]        $expectedHeaderValue
     */
    public function testValidWithAddedHeader($headerName, $headerValue, $expectedHeaderValue): void
    {
        $message = $this->classToTest;
        $messageClone = clone $message;
        $newMessage = $message->withAddedHeader($headerName, $headerValue);

        $this->assertImmutable($messageClone, $message, $newMessage);
        self::assertEquals(
            $expectedHeaderValue,
            $newMessage->getHeader($headerName),
            'getHeader does not match header set in withAddedHeader'
        );
    }

    /**
     * @dataProvider provideValidHeaderCases
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     */
    public function testHasHeader($headerName, $headerValue): void
    {
        $message = $this->classToTest;

        self::assertFalse($message->hasHeader($headerName));

        $newMessage = $message->withHeader($headerName, $headerValue);

        self::assertTrue($newMessage->hasHeader($headerName));
    }

    /**
     * @dataProvider provideValidHeaderCases
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     * @param string[]        $expectedHeaderValue
     * @param string          $expectedHeaderLine
     */
    public function testGetHeaderLine($headerName, $headerValue, $expectedHeaderValue, $expectedHeaderLine): void
    {
        $message = $this->classToTest;
        $newMessage = $message->withHeader($headerName, $headerValue);

        self::assertEquals($expectedHeaderLine, $newMessage->getHeaderLine($headerName));
    }

    /**
     * @dataProvider provideValidHeaderCases
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     * @param string[]        $expectedHeaderValue
     */
    public function testGetHeaders($headerName, $headerValue, $expectedHeaderValue): void
    {
        $message = $this->classToTest;
        $newMessage = $message->withHeader($headerName, $headerValue);

        self::assertEquals([$headerName => $expectedHeaderValue], $newMessage->getHeaders());
    }

    /**
     * @dataProvider provideValidHeaderCases
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     */
    public function testWithoutHeader($headerName, $headerValue): void
    {
        $message = $this->classToTest;
        $messageWithHeader = $message->withHeader($headerName, $headerValue);
        $messageClone = clone $messageWithHeader;

        self::assertTrue($messageWithHeader->hasHeader($headerName));

        $newMessage = $messageWithHeader->withoutHeader($headerName);

        $this->assertImmutable($messageClone, $messageWithHeader, $newMessage);
        self::assertFalse($newMessage->hasHeader($headerName));
        self::assertEquals($message, $newMessage);
    }

    /**
     * @return array<string, array<int, array<int|string, string>|int|string>>
     */
    public function provideValidHeaderCases(): iterable
    {
        return [
            // Description => [header name, header value, getHeader(), getHeaderLine()],
            'Basic: value' => ['Basic', 'value', ['value'], 'value'],
            'array value' => ['Basic', ['value'], ['value'], 'value'],
            'two value' => ['Basic', ['value1', 'value2'], ['value1', 'value2'], 'value1,value2'],
            'empty header value' => ['Bar', '', [''], ''],
            'array value with key' => ['foo', ['foo' => 'text/plain', 'bar' => 'application/json'], ['text/plain', 'application/json'], 'text/plain,application/json'],
            'Header with int' => ['HTTP__1', 'test', ['test'], 'test'],
            'Int header' => [1, 'test', ['test'], 'test'],
        ];
    }

    public function testWithBody(): void
    {
        $message = $this->classToTest;
        $messageClone = clone $message;

        /** @var \Mockery\MockInterface|\Psr\Http\Message\StreamInterface $expectedBodyMock */
        $expectedBodyMock = $this->mock(StreamInterface::class);
        $newMessage = $message->withBody($expectedBodyMock);

        $this->assertImmutable($messageClone, $message, $newMessage);
        self::assertEquals(
            $expectedBodyMock,
            $newMessage->getBody(),
            'getBody does not match body set in withBody'
        );
    }

    /**
     * DRY Assert header values.
     *
     * @param mixed[] $values
     */
    protected function assertValidHeaderValue($values): void
    {
        self::assertIsArray($values, 'header values MUST be an array of strings');
        self::assertContainsOnly('string', $values, true, 'MUST be an array of strings');
    }

    /**
     * @param object $messageClone
     * @param object $message
     * @param object $newMessage
     */
    protected function assertImmutable(object $messageClone, object $message, object $newMessage): void
    {
        self::assertEquals($messageClone, $message, 'Original message must be immutable');

        Immutable::assertImmutable($message, $newMessage);
    }
}
