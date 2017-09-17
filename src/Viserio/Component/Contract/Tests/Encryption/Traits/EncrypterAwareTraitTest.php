<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Encryption\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Encryption\Encrypter;
use Viserio\Component\Contract\Encryption\Traits\EncrypterAwareTrait;

class EncrypterAwareTraitTest extends MockeryTestCase
{
    use EncrypterAwareTrait;

    public function testGetAndSetEncrypter(): void
    {
        $this->setEncrypter($this->mock(Encrypter::class));

        self::assertInstanceOf(Encrypter::class, $this->encrypter);
    }
}
