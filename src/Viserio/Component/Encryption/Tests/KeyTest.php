<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Encryption\Exception\InvalidTypeException;
use Viserio\Component\Contract\Encryption\Security as SecurityContract;
use Viserio\Component\Encryption\HiddenString;
use Viserio\Component\Encryption\Key;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class KeyTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function testGenerateKey(): void
    {
        $key = KeyFactory::generateKey();

        self::assertInstanceOf(Key::class, $key);
    }

    public function testDeriveKey(): void
    {
        $key = KeyFactory::deriveKey(
            new HiddenString('apple'),
            "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f"
        );

        self::assertInstanceOf(Key::class, $key);
        self::assertSame(
            $key->getRawKeyMaterial(),
            "\x79\x12\x36\xc1\xf0\x6b\x73\xbd\xaa\x88\x89\x80\xe3\x2c\x4b\xdb" .
            "\x25\xd1\xf9\x39\xe5\xf7\x13\x30\x5c\xd8\x4c\x50\x22\xcc\x96\x6e"
        );
    }

    public function testInvalidKeyLevels(): void
    {
        try {
            KeyFactory::deriveKey(
                new HiddenString('apple'),
                "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f",
                'INVALID SECURITY LEVEL IDENTIFIER SHOULD HAVE USED A CONSTANT INSTEAD'
            );
            self::fail('Argon2 should fail on invalid.');
        } catch (InvalidTypeException $ex) {
            self::assertSame(
                'Invalid security level for Argon2i.',
                $ex->getMessage()
            );
        }
    }

    public function testKeyLevels(): void
    {
        $key = KeyFactory::deriveKey(
            new HiddenString('apple'),
            "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f",
            SecurityContract::KEY_MODERATE
        );

        self::assertSame(
            \sodium_bin2hex($key->getRawKeyMaterial()),
            'b5b21bb729b14cecca8e9d8e5811a09f0b4cb3fd4271ebf6f416ec855b6cd286'
        );

        $key = KeyFactory::deriveKey(
            new HiddenString('apple'),
            "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f",
            SecurityContract::KEY_SENSITIVE
        );

        self::assertSame(
            \sodium_bin2hex($key->getRawKeyMaterial()),
            'd2d76bb8f27dadcc2820515dee41e2e3946f489e5e0635c987815c06c3baee95'
        );
    }

    public function testExportAndImportKey(): void
    {
        $key          = KeyFactory::generateKey();
        $hiddenString = KeyFactory::exportToHiddenString($key);
        $loadedKey    = KeyFactory::importFromHiddenString($hiddenString);

        self::assertSame($key->getRawKeyMaterial(), $loadedKey->getRawKeyMaterial());
    }
}
