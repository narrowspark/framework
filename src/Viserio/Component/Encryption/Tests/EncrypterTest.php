<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Encryption\Exception\InvalidMessageException;
use Viserio\Component\Contracts\Encryption\Security as SecurityContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Encryption\HiddenString;
use Viserio\Component\Encryption\Key;

class EncrypterTest extends TestCase
{
    private const PHP_VERSION_PREFIX = 'AHACA';

    /**
     * @var \Viserio\Component\Encryption\Encrypter
     */
    private $encrypter;

    protected function setUp()
    {
        parent::setUp();

        $key = new Key(new HiddenString(\str_repeat('A', 32)));

        $this->encrypter = new Encrypter($key);
    }

    public function testEncrypt()
    {
        $message = $this->encrypter->encrypt(new HiddenString('test message'));

        self::assertSame(\mb_strpos($message, self::PHP_VERSION_PREFIX), 0);

        $plain = $this->encrypter->decrypt($message);

        self::assertSame($plain->getString(), 'test message');
    }

    public function testEncryptEmpty()
    {
        $message = $this->encrypter->encrypt(new HiddenString(''));

        self::assertSame(\mb_strpos($message, self::PHP_VERSION_PREFIX), 0);

        $plain   = $this->encrypter->decrypt($message);

        self::assertSame($plain->getString(), '');
    }

    public function testRawEncrypt()
    {
        $message = $this->encrypter->encrypt(new HiddenString('test message'), '', true);

        self::assertSame(\mb_strpos($message, self::PHP_VERSION_PREFIX), 0);

        $plain   = $this->encrypter->decrypt($message, '', true);

        self::assertSame($plain->getString(), 'test message');
    }

    public function testEncryptFail()
    {
        $message = $this->encrypter->encrypt(
            new HiddenString('test message'),
            '',
            true
        );

        self::assertSame(\mb_strpos($message, self::PHP_VERSION_PREFIX), 0);

        $r           = \random_int(0, \mb_strlen($message, '8bit') - 1);
        $message[$r] = \chr(
            \ord($message[$r])
            ^
            1 << random_int(0, 7)
        );

        try {
            $plain = $this->encrypter->decrypt($message, '', true);
            self::assertSame($plain, $message);
            $this->fail(
                'This should have thrown an InvalidMessage exception!'
            );
        } catch (InvalidMessageException $e) {
            self::assertTrue($e instanceof InvalidMessageException);
        }
    }

    public function testEncryptWithAd()
    {
        $message = $this->encrypter->encrypt(
            new HiddenString('test message'),
            'test'
        );

        self::assertSame(\mb_strpos($message, self::PHP_VERSION_PREFIX), 0);

        $plain = $this->encrypter->decrypt($message, 'test');
        self::assertSame($plain->getString(), 'test message');

        try {
            $this->encrypter->decrypt($message, 'wrong');
            $this->fail('AD did not change MAC.');
        } catch (InvalidMessageException $ex) {
            self::assertSame('Invalid message authentication code.', $ex->getMessage());
        }
    }
}
