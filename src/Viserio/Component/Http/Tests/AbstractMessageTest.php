<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Tests\Constraint\HttpProtocolVersion;
use Viserio\Component\Http\Tests\Constraint\Immutable;

/**
 * @internal
 */
abstract class AbstractMessageTest extends MockeryTestCase
{
    public $classToTest;

    // Test methods for default/empty instances
    public function testMessageImplementsInterface(): void
    {
        static::assertInstanceOf(MessageInterface::class, $this->classToTest);
    }

    public function testValidDefaultProtocolVersion(): void
    {
        $message = $this->classToTest;
        $version = $message->getProtocolVersion();

        static::assertInternalType('string', $version, 'getProtocolVersion must return a string');
        HttpProtocolVersion::assertValid($version);
    }

    public function testValidDefaultHeaders(): void
    {
        $message = $this->classToTest;
        $headers = $message->getHeaders();

        static::assertInternalType('array', $headers, "getHeaders an associative array of the message's headers");

        foreach ($headers as $name => $values) {
            static::assertInternalType('string', $name, 'Each key MUST be a header name');
            $this->assertValidHeaderValue($values);
        }
    }

    public function testValidNonExistHeader(): void
    {
        $message = $this->classToTest;
        $values  = $message->getHeader('not exist');

        $this->assertValidHeaderValue($values);
    }

    public function testValidNonExistHeaderLine(): void
    {
        $message    = $this->classToTest;
        $headerLine = $message->getHeaderLine('not exist');

        static::assertInternalType('string', $headerLine, 'getHeaderLine must return a string');
        static::assertEmpty(
            $headerLine,
            'If the header does not appear in the message, this method MUST return an empty string'
        );
    }

    public function testValidDefaultBody(): void
    {
        $message = $this->classToTest;
        $body    = $message->getBody();

        static::assertInstanceOf(
            StreamInterface::class,
            $body,
            'getBody must return instance of Psr\Http\Message\StreamInterface'
        );
    }

    /**
     * @dataProvider validProtocolVersionProvider
     *
     * @param string $expectedVersion
     */
    public function testValidWithProtocolVersion($expectedVersion): void
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;
        $newMessage   = $message->withProtocolVersion($expectedVersion);

        static::assertEquals(
            '1.1',
            $messageClone->getProtocolVersion(),
            'getProtocolVersion does not match version set in withProtocolVersion'
        );

        static::assertEquals(
            $expectedVersion,
            $newMessage->getProtocolVersion(),
            'getProtocolVersion does not match version set in withProtocolVersion'
        );
    }

    public function validProtocolVersionProvider()
    {
        return [
            // Description => [version],
            '1.0' => ['1.0'],
            '1.1' => ['1.1'],
            '2.0' => ['2.0'],
        ];
    }

    /**
     * @dataProvider validHeaderProvider
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     * @param string[]        $expectedHeaderValue
     */
    public function testValidWithHeader($headerName, $headerValue, $expectedHeaderValue): void
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;

        $newMessage = $message->withHeader($headerName, $headerValue);

        $this->assertImmutable($messageClone, $message, $newMessage);
        static::assertEquals(
            $expectedHeaderValue,
            $newMessage->getHeader($headerName),
            'getHeader does not match header set in withHeader'
        );
    }

    /**
     * @dataProvider validHeaderProvider
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     * @param string[]        $expectedHeaderValue
     */
    public function testValidWithAddedHeader($headerName, $headerValue, $expectedHeaderValue): void
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;
        $newMessage   = $message->withAddedHeader($headerName, $headerValue);

        $this->assertImmutable($messageClone, $message, $newMessage);
        static::assertEquals(
            $expectedHeaderValue,
            $newMessage->getHeader($headerName),
            'getHeader does not match header set in withAddedHeader'
        );
    }

    /**
     * @dataProvider validHeaderProvider
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     */
    public function testHasHeader($headerName, $headerValue): void
    {
        $message = $this->classToTest;

        static::assertFalse($message->hasHeader($headerName));

        $newMessage = $message->withHeader($headerName, $headerValue);

        static::assertTrue($newMessage->hasHeader($headerName));
    }

    /**
     * @dataProvider validHeaderProvider
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     * @param string[]        $expectedHeaderValue
     * @param string          $expectedHeaderLine
     */
    public function testGetHeaderLine($headerName, $headerValue, $expectedHeaderValue, $expectedHeaderLine): void
    {
        $message    = $this->classToTest;
        $newMessage = $message->withHeader($headerName, $headerValue);

        static::assertEquals($expectedHeaderLine, $newMessage->getHeaderLine($headerName));
    }

    /**
     * @dataProvider validHeaderProvider
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     * @param string[]        $expectedHeaderValue
     */
    public function testGetHeaders($headerName, $headerValue, $expectedHeaderValue): void
    {
        $message    = $this->classToTest;
        $newMessage = $message->withHeader($headerName, $headerValue);

        static::assertEquals([$headerName => $expectedHeaderValue], $newMessage->getHeaders());
    }

    /**
     * @dataProvider validHeaderProvider
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     */
    public function testWithoutHeader($headerName, $headerValue): void
    {
        $message           = $this->classToTest;
        $messageWithHeader = $message->withHeader($headerName, $headerValue);
        $messageClone      = clone $messageWithHeader;

        static::assertTrue($messageWithHeader->hasHeader($headerName));

        $newMessage = $messageWithHeader->withoutHeader($headerName);

        $this->assertImmutable($messageClone, $messageWithHeader, $newMessage);
        static::assertFalse($newMessage->hasHeader($headerName));
        static::assertEquals($message, $newMessage);
    }

    public function validHeaderProvider()
    {
        return [
            // Description => [header name, header value, getHeader(), getHeaderLine()],
            'Basic: value' => ['Basic', 'value', ['value'], 'value'],
            'array value'  => ['Basic', ['value'], ['value'], 'value'],
            'two value'    => ['Basic', ['value1', 'value2'], ['value1', 'value2'], 'value1,value2'],
        ];
    }

    public function testWithBody(): void
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;

        $expectedBody = $this->mock(StreamInterface::class);
        $newMessage   = $message->withBody($expectedBody);

        $this->assertImmutable($messageClone, $message, $newMessage);
        static::assertEquals(
            $expectedBody,
            $newMessage->getBody(),
            'getBody does not match body set in withBody'
        );
    }

    /**
     * DRY Assert header values.
     *
     * @param string[] $values
     */
    protected function assertValidHeaderValue($values): void
    {
        static::assertInternalType('array', $values, 'header values MUST be an array of strings');
        static::assertContainsOnly('string', $values, true, 'MUST be an array of strings');
    }

    /**
     * @param object $messageClone
     * @param object $message
     * @param object $newMessage
     */
    protected function assertImmutable($messageClone, $message, $newMessage): void
    {
        static::assertEquals($messageClone, $message, 'Original message must be immutable');
        Immutable::assertImmutable($message, $newMessage);
    }
}
