<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extension;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Twig\Extensions\TranslatorExtension;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;

class TranslatorExtensionTest extends MockeryTestCase
{
    public function testGetFunctions()
    {
        $extension = new TranslatorExtension($this->mock(TranslatorContract::class));
        $functions = $extension->getFunctions();

        self::assertEquals('trans', $functions[0]->getName());
        self::assertEquals('trans', $functions[0]->getCallable()[1]);
    }

    public function testGetFilters()
    {
        $extension = new TranslatorExtension($this->mock(TranslatorContract::class));
        $filter    = $extension->getFilters();

        self::assertEquals('trans', $filter[0]->getName());
        self::assertEquals('trans', $filter[0]->getCallable()[1]);
    }

    public function testGetName()
    {
        self::assertEquals(
            'Viserio_Provider_Twig_Extension_Translator',
            (new TranslatorExtension($this->mock(TranslatorContract::class)))->getName()
        );
    }
}
