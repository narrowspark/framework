<?php
declare(strict_types=1);
namespace Viserio\Contracts\Encryption\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Contracts\Encryption\Encrypter;
use Viserio\Contracts\Encryption\Traits\EncrypterAwareTrait;

class EncrypterAwareTraitTest extends TestCase
{
    use MockeryTrait;
    use EncrypterAwareTrait;

    public function testGetAndSetEncrypter()
    {
        $this->setEncrypter($this->mock(Encrypter::class));

        self::assertInstanceOf(Encrypter::class, $this->getEncrypter());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Encrypter is not set up.
     */
    public function testGetEncrypterThrowExceptionIfEncrypterIsNotSet()
    {
        $this->getEncrypter();
    }
}
