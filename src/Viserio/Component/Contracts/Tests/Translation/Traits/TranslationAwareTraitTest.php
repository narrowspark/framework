<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Translation\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Translation\Traits\TranslatorAwareTrait;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;

class TranslationAwareTraitTest extends MockeryTestCase
{
    use TranslatorAwareTrait;

    public function testGetAndSetTranslator()
    {
        $this->setTranslator($this->mock(TranslatorContract::class));

        self::assertInstanceOf(TranslatorContract::class, $this->getTranslator());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Translator is not set up.
     */
    public function testGetTranslatorThrowExceptionIfTranslatorIsNotSet()
    {
        $this->getTranslator();
    }
}
