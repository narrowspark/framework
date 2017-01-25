<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extensions;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Twig\Extensions\TranslatorExtension;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;

class TranslatorExtensionTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testGetFunctions()
    {
        $extension = new TranslatorExtension($this->mock(TranslatorContract::class));
        $functions = $extension->getFunctions();

        $this->assertEquals('trans', $functions[0]->getName());
        $this->assertEquals('trans', $functions[0]->getCallable()[1]);

        $this->assertEquals('trans_choice', $functions[1]->getName());
        $this->assertEquals('transChoice', $functions[1]->getCallable()[1]);
    }

    public function testGetFilters()
    {
        $extension = new TranslatorExtension($this->mock(TranslatorContract::class));
        $filter    = $extension->getFilters();

        $this->assertEquals('trans', $filter[0]->getName());
        $this->assertEquals('trans', $filter[0]->getCallable()[1]);

        $this->assertEquals('trans_choice', $filter[1]->getName());
        $this->assertEquals('transChoice', $filter[1]->getCallable()[1]);
    }

    public function testGetName()
    {
        $this->assertEquals(
            'Viserio_Bridge_Twig_Extension_Translator',
            (new TranslatorExtension($this->mock(TranslatorContract::class)))->getName()
        );
    }
}
