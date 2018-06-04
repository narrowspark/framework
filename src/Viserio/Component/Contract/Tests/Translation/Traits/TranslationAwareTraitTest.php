<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Translation\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Translation\Traits\TranslatorAwareTrait;
use Viserio\Component\Contract\Translation\Translator as TranslatorContract;

/**
 * @internal
 */
final class TranslationAwareTraitTest extends MockeryTestCase
{
    use TranslatorAwareTrait;

    public function testGetAndSetTranslator(): void
    {
        $this->setTranslator($this->mock(TranslatorContract::class));

        $this->assertInstanceOf(TranslatorContract::class, $this->getTranslator());
    }

    public function testGetTranslatorThrowExceptionIfTranslatorIsNotSet(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Translator is not set up.');

        $this->getTranslator();
    }
}
