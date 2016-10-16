<?php
declare(strict_types=1);
namespace Viserio\Contracts\Translation\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Translation\Traits\TranslationAwareTrait;
use Viserio\Contracts\Translation\Translator as TranslatorContract;

class TranslationAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
    use TranslationAwareTrait;

    public function testGetAndSetTranslator()
    {
        $this->setTranslator($this->mock(TranslatorContract::class));

        $this->assertInstanceOf(TranslatorContract::class, $this->getTranslator());
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
