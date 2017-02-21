<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extensions;

use Mockery as Mock;
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

        self::assertEquals('trans_choice', $functions[1]->getName());
        self::assertEquals('transChoice', $functions[1]->getCallable()[1]);
    }

    public function testGetFilters()
    {
        $extension = new TranslatorExtension($this->mock(TranslatorContract::class));
        $filter    = $extension->getFilters();

        self::assertEquals('trans', $filter[0]->getName());
        self::assertEquals('trans', $filter[0]->getCallable()[1]);

        self::assertEquals('trans_choice', $filter[1]->getName());
        self::assertEquals('transChoice', $filter[1]->getCallable()[1]);
    }

    public function testGetName()
    {
        self::assertEquals(
            'Viserio_Bridge_Twig_Extension_Translator',
            (new TranslatorExtension($this->mock(TranslatorContract::class)))->getName()
        );
    }
}
