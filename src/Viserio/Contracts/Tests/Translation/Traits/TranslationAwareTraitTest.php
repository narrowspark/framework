<?php
declare(strict_types=1);
namespace Viserio\Contracts\Translation\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Translation\Traits\TranslatorAwareTrait;
use Viserio\Contracts\Translation\Translator as TranslatorContract;
use PHPUnit\Framework\TestCase;

class TranslationAwareTraitTest extends TestCase
{
    use MockeryTrait;
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
