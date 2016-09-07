<?php
declare(strict_types=1);
namespace Viserio\Http\Tests;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\Http\Tests\Constraint\HttpProtocolVersion;
use Viserio\Http\Tests\Constraint\Immutable;

abstract class AbstractMessageTest extends \PHPUnit_Framework_TestCase
{
    public $classToTest;

    // Test methods for default/empty instances
    public function testMessageImplementsInterface()
    {
        $this->assertInstanceOf(MessageInterface::class, $this->classToTest);
    }

    public function testValidDefaultProtocolVersion()
    {
        $message = $this->classToTest;
        $version = $message->getProtocolVersion();

        $this->assertInternalType('string', $version, 'getProtocolVersion must return a string');
        HttpProtocolVersion::assertValid($version);
    }

    public function testValidDefaultHeaders()
    {
        $message = $this->classToTest;
        $headers = $message->getHeaders();

        $this->assertInternalType('array', $headers, "getHeaders an associative array of the message's headers");

        foreach ($headers as $name => $values) {
            $this->assertInternalType('string', $name, 'Each key MUST be a header name');
            $this->assertValidHeaderValue($values);
        }
    }

    public function testValidNonExistHeader()
    {
        $message = $this->classToTest;
        $values = $message->getHeader('not exist');

        $this->assertValidHeaderValue($values);
    }

    public function testValidNonExistHeaderLine()
    {
        $message = $this->classToTest;
        $headerLine = $message->getHeaderLine('not exist');

        $this->assertInternalType('string', $headerLine, 'getHeaderLine must return a string');
        $this->assertEmpty(
            $headerLine,
            'If the header does not appear in the message, this method MUST return an empty string'
        );
    }

    public function testValidDefaultBody()
    {
        $message = $this->classToTest;
        $body = $message->getBody();

        $this->assertInstanceOf(
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
        $message = $this->classToTest;
        $messageClone = clone $message;
        $newMessage = $message->withProtocolVersion($expectedVersion);

        $this->assertEquals(
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
        $message = $this->classToTest;
        $messageClone = clone $message;

        $newMessage = $message->withHeader($headerName, $headerValue);

        $this->assertImmutable($messageClone, $message, $newMessage);
        $this->assertEquals(
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
        $message = $this->classToTest;
        $messageClone = clone $message;
        $newMessage = $message->withAddedHeader($headerName, $headerValue);

        $this->assertImmutable($messageClone, $message, $newMessage);
        $this->assertEquals(
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

        $this->assertFalse($message->hasHeader($headerName));

        $newMessage = $message->withHeader($headerName, $headerValue);

        $this->assertTrue($newMessage->hasHeader($headerName));
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
        $message = $this->classToTest;
        $newMessage = $message->withHeader($headerName, $headerValue);

        $this->assertEquals($expectedHeaderLine, $newMessage->getHeaderLine($headerName));
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
        $message = $this->classToTest;
        $newMessage = $message->withHeader($headerName, $headerValue);

        $this->assertEquals([$headerName => $expectedHeaderValue], $newMessage->getHeaders());
    }

    /**
     * @dataProvider validHeaderProvider
     *
     * @param string          $headerName
     * @param string|string[] $headerValue
     */
    public function testWithoutHeader($headerName, $headerValue)
    {
        $message = $this->classToTest;
        $messageWithHeader = $message->withHeader($headerName, $headerValue);
        $messageClone = clone $messageWithHeader;

        $this->assertTrue($messageWithHeader->hasHeader($headerName));

        $newMessage = $messageWithHeader->withoutHeader($headerName);

        $this->assertImmutable($messageClone, $messageWithHeader, $newMessage);
        $this->assertFalse($newMessage->hasHeader($headerName));
        $this->assertEquals($message, $newMessage);
    }

    public function validHeaderProvider()
    {
        return [
            // Description => [header name, header value, getHeader(), getHeaderLine()],
            'Basic: value' => ['Basic', 'value', ['value'], 'value'],
            'array value' => ['Basic', ['value'], ['value'], 'value'],
            'two value' => ['Basic', ['value1', 'value2'], ['value1', 'value2'], 'value1,value2'],
        ];
    }

    public function testWithBody()
    {
        $message = $this->classToTest;
        $messageClone = clone $message;
        /** @var StreamInterface $expectedBody */
        $expectedBody = $this->createMock(StreamInterface::class);
        $newMessage = $message->withBody($expectedBody);

        $this->assertImmutable($messageClone, $message, $newMessage);
        $this->assertEquals(
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
        $this->assertInternalType('array', $values, 'header values MUST be an array of strings');
        $this->assertContainsOnly('string', $values, true, 'MUST be an array of strings');
    }

    /**
     * @param object $messageClone
     * @param object $message
     * @param object $newMessage
     */
    protected function assertImmutable($messageClone, $message, $newMessage)
    {
        $this->assertEquals($messageClone, $message, 'Original message must be immutable');
        Immutable::assertImmutable($message, $newMessage);
    }
}
