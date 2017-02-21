<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Encryption\Encrypter;
use Viserio\Component\Contracts\Encryption\Traits\EncrypterAwareTrait;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;

class EncrypterAwareTraitTest extends MockeryTestCase
{
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
