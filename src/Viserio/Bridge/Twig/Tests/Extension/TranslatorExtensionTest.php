<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extension;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader as TwigArrayLoader;
use Viserio\Bridge\Twig\Extension\TranslatorExtension;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\Translation\Formatter\IntlMessageFormatter;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Component\Translation\Translator;

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
            'Viserio_Bridge_Twig_Extension_Translator',
            (new TranslatorExtension($this->mock(TranslatorContract::class)))->getName()
        );
    }

    public function testEscaping()
    {
        $output = $this->getTemplate('{% trans %}Percent: %value%%% (%msg%){% endtrans %}')
            ->render(['value' => 12, 'msg' => 'approx.']);

        self::assertEquals('Percent: 12% (approx.)', $output);
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage Unexpected token. Twig was looking for the "with", "from", or "into" keyword in "index" at line 3.
     */
    public function testTransUnknownKeyword()
    {
        $output = $this->getTemplate("{% trans \n\nfoo %}{% endtrans %}")->render();
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage A message inside a trans tag must be a simple text in "index" at line 2.
     */
    public function testTransComplexBody()
    {
        $output = $this->getTemplate("{% trans %}\n{{ 1 + 2 }}{% endtrans %}")->render();
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage A message inside a transchoice tag must be a simple text in "index" at line 2.
     */
    public function testTransChoiceComplexBody()
    {
        $output = $this->getTemplate("{% transchoice count %}\n{{ 1 + 2 }}{% endtranschoice %}")->render();
    }

    protected function getTemplate($template, $translator = null)
    {
        if ($translator === null) {
            $translator = new Translator(new MessageCatalogue('en'), new IntlMessageFormatter());
        }

        if (is_array($template)) {
            $loader = new TwigArrayLoader($template);
        } else {
            $loader = new TwigArrayLoader(['index' => $template]);
        }

        $twig = new Environment($loader, ['debug' => true, 'cache' => false]);
        $twig->addExtension(new TranslatorExtension($translator));

        return $twig->loadTemplate('index');
    }
}
