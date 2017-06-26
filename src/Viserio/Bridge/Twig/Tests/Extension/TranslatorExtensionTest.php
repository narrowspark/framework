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
        $output = $this->getTemplate('{% trans %}Percent: {value}% ({msg}){% endtrans %}')->render(['value' => 12, 'msg' => 'approx.']);

        self::assertEquals('Percent: 12% (approx.)', $output);
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage A message inside a trans tag must be a simple text in "index" at line 2.
     */
    public function testTransComplexBody()
    {
        $this->getTemplate("{% trans %}\n{{ 1 + 2 }}{% endtrans %}")->render();
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage A message inside a trans tag must be a simple text in "index" at line 2.
     */
    public function testTransComplexBodyWithCount()
    {
        $output = $this->getTemplate("{% trans %}\n{{ 1 + 2 }}{% endtrans %}")->render();
    }

    /**
     * @dataProvider getTransTests
     *
     * @param mixed $template
     * @param mixed $expected
     */
    public function testTransa($template, $expected, array $variables = [])
    {
        if ($expected != $this->getTemplate($template)->render($variables)) {
            echo $template . "\n";

            $loader = new TwigArrayLoader(['index' => $template]);
            $twig   = new Environment($loader, ['debug' => true, 'cache' => false]);

            $twig->addExtension(new TranslatorExtension($this->getTranslationManager()));

            echo $twig->compile($twig->parse($twig->tokenize($twig->getLoader()->getSourceContext('index')))) . "\n\n";

            self::assertEquals($expected, $this->getTemplate($template)->render($variables));
        }

        self::assertEquals($expected, $this->getTemplate($template)->render($variables));
    }

    public function getTransTests()
    {
        return [
            // trans tag
            ['{% trans %}Hello{% endtrans %}', 'Hello'],
            ['{% trans %}{name}{% endtrans %}', 'Narrospark', ['name' => 'Narrospark']],
            ['{% trans from "elsewhere" %}Hello{% endtrans %}', 'Hello'],
            ['{% trans %}Hello {name}{% endtrans %}', 'Hello Narrospark', ['name' => 'Narrospark']],
            ['{% trans with { \'name\': \'Narrospark\' } %}Hello {name}{% endtrans %}', 'Hello Narrospark'],
            ['{% set vars = { \'name\': \'Narrospark\' } %}{% trans with vars %}Hello {name}{% endtrans %}', 'Hello Narrospark'],
            ['{% trans into "fr"%}Hello{% endtrans %}', 'Hello'],
            ['{% trans from "messages" %}{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}}{% endtrans %}', 'No candy left', ['count' => 0], ],
            ['{% trans %}{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}}{% endtrans %}', 'Got 5 candies left', ['count' => 5], ],
            ['{% trans %}{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}} ({name}){% endtrans %}', 'Got 5 candies left (Narrospark)', ['count' => 5, 'name' => 'Narrospark'], ],
            ['{% trans with { \'name\': \'Narrospark\' } %}{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}} ({name}){% endtrans %}', 'Got 5 candies left (Narrospark)', ['count' => 5], ],
            ['{% trans into "fr"%}{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}}{% endtrans %}', 'No candy left', ['count' => 0], ],
            // trans filter
            ['{{ "Hello"|trans }}', 'Hello'],
            ['{{ name|trans }}', 'Narrospark', ['name' => 'Narrospark']],
            ['{{ hello|trans({ \'name\': \'Narrospark\' }) }}', 'Hello Narrospark', ['hello' => 'Hello {name}']],
            ['{% set vars = { \'name\': \'Narrospark\' } %}{{ hello|trans(vars) }}', 'Hello Narrospark', ['hello' => 'Hello {name}']],
            ['{{ "Hello"|trans({}, "messages", "fr") }}', 'Hello'],
            // // trans filter
            // ['{{ "{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}}"|trans(count) }}', 'Got 5 candies left', ['count' => 5]],
            // ['{{ text|trans(5, {\'name\': \'Narrospark\'}) }}', 'Got 5 candies left (Narrospark)', ['text' => '{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}} ({name})']],
            // ['{{ "{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}}"|trans(count, {}, "messages", "fr") }}', 'Got 5 candies left', ['count' => 5]],
        ];
    }

    private function getTemplate($template)
    {
        $translator = $this->getTranslationManager();

        if (is_array($template)) {
            $loader = new TwigArrayLoader($template);
        } else {
            $loader = new TwigArrayLoader(['index' => $template]);
        }

        $twig = new Environment($loader, ['debug' => true, 'cache' => false]);
        $twig->addExtension(new TranslatorExtension($translator));

        return $twig->loadTemplate('index');
    }

    private function getTranslationManager(): TranslationManager
    {
        $translator = new TranslationManager(new IntlMessageFormatter());
        $translator->addMessageCatalogue(new MessageCatalogue('en'));
        $translator->addMessageCatalogue(new MessageCatalogue('fr'));

        return $translator;
    }
}
