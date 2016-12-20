<?php
declare(strict_types=1);
namespace Viserio\Http\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\Http\Tests\Constraint\HttpProtocolVersion;
use Viserio\Http\Tests\Constraint\Immutable;

abstract class AbstractMessageTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public $classToTest;

    // Test methods for default/empty instances
    public function testMessageImplementsInterface()
    {
        self::assertInstanceOf(MessageInterface::class, $this->classToTest);
    }

    public function testValidDefaultProtocolVersion()
    {
        $message = $this->classToTest;
        $version = $message->getProtocolVersion();

        self::assertInternalType('string', $version, 'getProtocolVersion must return a string');
        HttpProtocolVersion::assertValid($version);
    }

    public function testValidDefaultHeaders()
    {
        $message = $this->classToTest;
        $headers = $message->getHeaders();

        self::assertInternalType('array', $headers, "getHeaders an associative array of the message's headers");

        foreach ($headers as $name => $values) {
            self::assertInternalType('string', $name, 'Each key MUST be a header name');
            self::assertValidHeaderValue($values);
        }
    }

    public function testValidNonExistHeader()
    {
        $message = $this->classToTest;
        $values  = $message->getHeader('not exist');

        self::assertValidHeaderValue($values);
    }

    public function testValidNonExistHeaderLine()
    {
        $message    = $this->classToTest;
        $headerLine = $message->getHeaderLine('not exist');

        self::assertInternalType('string', $headerLine, 'getHeaderLine must return a string');
        self::assertEmpty(
            $headerLine,
            'If the header does not appear in the message, this method MUST return an empty string'
        );
    }

    public function testValidDefaultBody()
    {
        $message = $this->classToTest;
        $body    = $message->getBody();

        self::assertInstanceOf(
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
    public function testValidWithProtocolVersion($expectedVersion)
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;
        $newMessage   = $message->withProtocolVersion($expectedVersion);

        self::assertEquals(
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
    public function testValidWithHeader($headerName, $headerValue, $expectedHeaderValue)
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;

        $newMessage = $message->withHeader($headerName, $headerValue);

        self::assertImmutable($messageClone, $message, $newMessage);
        self::assertEquals(
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
    public function testValidWithAddedHeader($headerName, $headerValue, $expectedHeaderValue)
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;
        $newMessage   = $message->withAddedHeader($headerName, $headerValue);

        self::assertImmutable($messageClone, $message, $newMessage);
        self::assertEquals(
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
    public function testHasHeader($headerName, $headerValue)
    {
        $message = $this->classToTest;

        self::assertFalse($message->hasHeader($headerName));

        $newMessage = $message->withHeader($headerName, $headerValue);

        self::assertTrue($newMessage->hasHeader($headerName));
    }

    /**
     * @dataProvider validHeaderProvider
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     * @param string[]        $expectedHeaderValue
     * @param string          $expectedHeaderLine
     */
    public function testGetHeaderLine($headerName, $headerValue, $expectedHeaderValue, $expectedHeaderLine)
    {
        $message    = $this->classToTest;
        $newMessage = $message->withHeader($headerName, $headerValue);

        self::assertEquals($expectedHeaderLine, $newMessage->getHeaderLine($headerName));
    }

    /**
     * @dataProvider validHeaderProvider
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     * @param string[]        $expectedHeaderValue
     */
    public function testGetHeaders($headerName, $headerValue, $expectedHeaderValue)
    {
        $message    = $this->classToTest;
        $newMessage = $message->withHeader($headerName, $headerValue);

        self::assertEquals([$headerName => $expectedHeaderValue], $newMessage->getHeaders());
    }

    /**
     * @dataProvider validHeaderProvider
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     */
    public function testWithoutHeader($headerName, $headerValue)
    {
        $message           = $this->classToTest;
        $messageWithHeader = $message->withHeader($headerName, $headerValue);
        $messageClone      = clone $messageWithHeader;

        self::assertTrue($messageWithHeader->hasHeader($headerName));

        $newMessage = $messageWithHeader->withoutHeader($headerName);

        self::assertImmutable($messageClone, $messageWithHeader, $newMessage);
        self::assertFalse($newMessage->hasHeader($headerName));
        self::assertEquals($message, $newMessage);
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

    public function testWithBody()
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;

        $expectedBody = $this->mock(StreamInterface::class);
        $newMessage   = $message->withBody($expectedBody);

        self::assertImmutable($messageClone, $message, $newMessage);
        self::assertEquals(
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
    protected function assertValidHeaderValue($values)
    {
        self::assertInternalType('array', $values, 'header values MUST be an array of strings');
        self::assertContainsOnly('string', $values, true, 'MUST be an array of strings');
    }

    /**
     * @param object $messageClone
     * @param object $message
     * @param object $newMessage
     */
    protected function assertImmutable($messageClone, $message, $newMessage)
    {
        self::assertEquals($messageClone, $message, 'Original message must be immutable');
        Immutable::assertImmutable($message, $newMessage);
    }
}
