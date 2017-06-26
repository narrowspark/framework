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
        $output = $this->getTemplate('{% trans %}Percent: %value%%% (%msg%){% endtrans %}')->render(['value' => 12, 'msg' => 'approx.']);

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
        $output = $this->getTemplate("{% trans count %}\n{{ 1 + 2 }}{% endtrans %}")->render();
    }

    /**
     * @dataProvider getTransTests
     *
     * @param mixed $template
     * @param mixed $expected
     */
    public function testTrans($template, $expected, array $variables = [])
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
            ['{% trans %}{name}{% endtrans %}', 'Symfony', ['name' => 'Symfony']],
            // ['{% trans from elsewhere %}Hello{% endtrans %}', 'Hello'],
            ['{% trans %}Hello {name}{% endtrans %}', 'Hello Symfony', ['name' => 'Symfony']],
            // ['{% trans with { \'{name}\': \'Symfony\' } %}Hello {name}{% endtrans %}', 'Hello Symfony'],
            // ['{% set vars = { \'{name}\': \'Symfony\' } %}{% trans with vars %}Hello {name}{% endtrans %}', 'Hello Symfony'],
            // ['{% trans into "fr"%}Hello{% endtrans %}', 'Hello'],
            // ['{% trans count from "messages" %}{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples{% endtrans %}',
            //     'There is no apples', ['count' => 0], ],
            // ['{% trans count %}{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples{% endtrans %}',
            //     'There is 5 apples', ['count' => 5], ],
            // ['{% trans count %}{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples ({name}){% endtrans %}',
            //     'There is 5 apples (Symfony)', ['count' => 5, 'name' => 'Symfony'], ],
            // ['{% trans count with { \'{name}\': \'Symfony\' } %}{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples ({name}){% endtrans %}',
            //     'There is 5 apples (Symfony)', ['count' => 5], ],
            // ['{% trans count into "fr"%}{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples{% endtrans %}',
            //     'There is no apples', ['count' => 0], ],
            // ['{% trans 5 into "fr"%}{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples{% endtrans %}',
            //     'There is 5 apples', ],
            // // trans filter
            // ['{{ "Hello"|trans }}', 'Hello'],
            // ['{{ name|trans }}', 'Symfony', ['name' => 'Symfony']],
            // ['{{ hello|trans({ \'{name}\': \'Symfony\' }) }}', 'Hello Symfony', ['hello' => 'Hello {name}']],
            // ['{% set vars = { \'{name}\': \'Symfony\' } %}{{ hello|trans(vars) }}', 'Hello Symfony', ['hello' => 'Hello {name}']],
            // ['{{ "Hello"|trans({}, "messages", "fr") }}', 'Hello'],
            // // trans filter
            // ['{{ "{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples"|trans(count) }}', 'There is 5 apples', ['count' => 5]],
            // ['{{ text|trans(5, {\'{name}\': \'Symfony\'}) }}', 'There is 5 apples (Symfony)', ['text' => '{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples ({name})']],
            // ['{{ "{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples"|trans(count, {}, "messages", "fr") }}', 'There is 5 apples', ['count' => 5]],
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

        return $translator;
    }
}
