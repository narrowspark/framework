<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extension;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader as TwigArrayLoader;
use Viserio\Bridge\Twig\Extension\TranslatorExtension;
use Viserio\Component\Translation\Formatter\IntlMessageFormatter;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Component\Translation\TranslationManager;

class TranslatorExtensionTest extends MockeryTestCase
{
    public function testGetFunctions()
    {
        $extension = new TranslatorExtension($this->getTranslationManager());
        $functions = $extension->getFunctions();

        self::assertEquals('trans', $functions[0]->getName());
        self::assertEquals('trans', $functions[0]->getCallable()[1]);
    }

    public function testGetFilters()
    {
        $extension = new TranslatorExtension($this->getTranslationManager());
        $filter    = $extension->getFilters();

        self::assertEquals('trans', $filter[0]->getName());
        self::assertEquals('trans', $filter[0]->getCallable()[1]);
    }

    public function testGetName()
    {
        self::assertEquals(
            'Viserio_Bridge_Twig_Extension_Translator',
            (new TranslatorExtension($this->getTranslationManager()))->getName()
        );
    }

    public function testEscaping()
    {
        $output = $this->renderTemplate('{% trans %}Percent: %value%%% (%msg%){% endtrans %}', ['value' => 12, 'msg' => 'approx.']);

        self::assertEquals('Percent: 12% (approx.)', $output);
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage Unexpected token. Twig was looking for the "with", "from", or "into" keyword in "index" at line 3.
     */
    public function testTransUnknownKeyword()
    {
        $this->renderTemplate("{% trans foo %}{% endtrans %}");
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage A message inside a trans tag must be a simple text in "index" at line 2.
     */
    public function testTransComplexBody()
    {
        $this->renderTemplate("{% trans %}\n{{ 1 + 2 }}{% endtrans %}");
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage A message inside a trans tag must be a simple text in "index" at line 2.
     */
    public function testTransComplexBodyWithCount()
    {
        $output = $this->renderTemplate("{% trans count %}\n{{ 1 + 2 }}{% endtrans %}");
    }

    private function renderTemplate($template, array $args = [])
    {
        $translator = $this->getTranslationManager();

        if (is_array($template)) {
            $loader = new TwigArrayLoader($template);
        } else {
            $loader = new TwigArrayLoader(['index' => $template]);
        }

        $twig = new Environment($loader, ['debug' => true, 'cache' => false]);
        $twig->addExtension(new TranslatorExtension($translator));

        return $twig->render('index', $args);
    }

    private function getTranslationManager(): TranslationManager
    {
        $translator = new TranslationManager(new IntlMessageFormatter());
        $translator->addMessageCatalogue(new MessageCatalogue('en'));

        return $translator;
    }
}
