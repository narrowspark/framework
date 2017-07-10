<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Translation\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Translation\Traits\TranslatorAwareTrait;
use Viserio\Component\Contract\Translation\Translator as TranslatorContract;

class TranslationAwareTraitTest extends MockeryTestCase
{
    use TranslatorAwareTrait;

    public function testGetAndSetTranslator(): void
    {
        $this->setTranslator($this->mock(TranslatorContract::class));

        self::assertInstanceOf(TranslatorContract::class, $this->getTranslator());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Translator is not set up.
     */
    public function testGetTranslatorThrowExceptionIfTranslatorIsNotSet(): void
    {
        $this->getTranslator();
    }
}
