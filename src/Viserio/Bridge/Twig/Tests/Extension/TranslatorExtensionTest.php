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

/**
 * @internal
 */
final class TranslatorExtensionTest extends MockeryTestCase
{
    public function testGetFunctions(): void
    {
        $extension = new TranslatorExtension($this->getTranslationManager());
        $functions = $extension->getFunctions();

        static::assertEquals('trans', $functions[0]->getName());
        static::assertEquals('trans', $functions[0]->getCallable()[1]);
    }

    public function testGetFilters(): void
    {
        $extension = new TranslatorExtension($this->getTranslationManager());
        $filter    = $extension->getFilters();

        static::assertEquals('trans', $filter[0]->getName());
        static::assertEquals('trans', $filter[0]->getCallable()[1]);
    }

    public function testGetName(): void
    {
        static::assertEquals(
            'Viserio_Bridge_Twig_Extension_Translator',
            (new TranslatorExtension($this->getTranslationManager()))->getName()
        );
    }

    public function testEscaping(): void
    {
        $output = $this->getTemplate('{% trans %}Percent: {value}% ({msg}){% endtrans %}')->render(['value' => 12, 'msg' => 'approx.']);

        static::assertEquals('Percent: 12% (approx.)', $output);
    }

    public function testTransComplexBody(): void
    {
        $this->expectException(\Twig\Error\SyntaxError::class);
        $this->expectExceptionMessage('A message inside a trans tag must be a simple text in "index" at line 2.');

        $this->getTemplate("{% trans %}\n{{ 1 + 2 }}{% endtrans %}")->render([]);
    }

    public function testTransComplexBodyWithCount(): void
    {
        $this->expectException(\Twig\Error\SyntaxError::class);
        $this->expectExceptionMessage('A message inside a trans tag must be a simple text in "index" at line 2.');

        $this->getTemplate("{% trans %}\n{{ 1 + 2 }}{% endtrans %}")->render([]);
    }

    /**
     * @dataProvider getTransTests
     *
     * @param mixed $template
     * @param mixed $expected
     * @param array $variables
     */
    public function testTransa($template, $expected, array $variables = []): void
    {
        if ($expected !== $this->getTemplate($template)->render($variables)) {
            echo $template . "\n";

            $loader = new TwigArrayLoader(['index' => $template]);
            $twig   = new Environment($loader, ['debug' => true, 'cache' => false]);

            $twig->addExtension(new TranslatorExtension($this->getTranslationManager()));

            echo $twig->compile($twig->parse($twig->tokenize($twig->getLoader()->getSourceContext('index')))) . "\n\n";

            static::assertEquals($expected, $this->getTemplate($template)->render($variables));
        }

        static::assertEquals($expected, $this->getTemplate($template)->render($variables));
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
            ['{% trans from "messages" %}{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}}{% endtrans %}', 'No candy left', ['count' => 0]],
            ['{% trans %}{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}}{% endtrans %}', 'Got 5 candies left', ['count' => 5]],
            ['{% trans %}{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}} ({name}){% endtrans %}', 'Got 5 candies left (Narrospark)', ['count' => 5, 'name' => 'Narrospark']],
            ['{% trans with { \'name\': \'Narrospark\' } %}{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}} ({name}){% endtrans %}', 'Got 5 candies left (Narrospark)', ['count' => 5]],
            ['{% trans into "fr"%}{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}}{% endtrans %}', 'No candy left', ['count' => 0]],
            ['{% trans %}{0,number,integer} monkeys on {1,number,integer} trees make {2,number} monkeys per tree{% endtrans %}', '4,560 monkeys on 123 trees make 37.073 monkeys per tree', [4560, 123, 4560 / 123]],
            // trans filter
            ['{{ "Hello"|trans }}', 'Hello'],
            ['{{ name|trans }}', 'Narrospark', ['name' => 'Narrospark']],
            ['{{ hello|trans({ \'name\': \'Narrospark\' }) }}', 'Hello Narrospark', ['hello' => 'Hello {name}']],
            ['{% set vars = { \'name\': \'Narrospark\' } %}{{ hello|trans(vars) }}', 'Hello Narrospark', ['hello' => 'Hello {name}']],
            ['{{ "Hello"|trans({}, "messages", "fr") }}', 'Hello'],
            ['{{ "{0,number,integer} monkeys on {1,number,integer} trees make {2,number} monkeys per tree"|trans({0 : 4560, 1 : 123, 2 : 4560/123}, "messages") }}', '4,560 monkeys on 123 trees make 37.073 monkeys per tree'],
            ['{{ "{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}}"|trans(count) }}', 'Got 5 candies left', ['count' => 5]],
            ['{{ text|trans({\'count\' : 5, \'name\': \'Narrospark\'}) }}', 'Got 5 candies left (Narrospark)', ['text' => '{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}} ({name})']],
            ['{{ "{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}}"|trans(count, "messages", "fr") }}', 'Got 5 candies left', ['count' => 5]],
        ];
    }

    public function testDefaultTranslationDomain(): void
    {
        $templates = [
            'index' => '
                {%- extends "base" %}
                {%- trans_default_domain "foo" %}
                {%- block content %}
                    {%- trans %}foo{% endtrans %}
                    {%- trans from "custom" %}foo{% endtrans %}
                    {{- "foo"|trans }}
                    {{- "foo"|trans({}, "custom") }}
                    {{- "foo"|trans() }}
                    {{- "foo"|trans({}, "custom") }}
                {% endblock %}
            ',
            'base' => '
                {%- block content "" %}
            ',
        ];

        $catalogue = new MessageCatalogue('en');
        $catalogue->set('foo', 'foo (messages)');
        $catalogue->set('foo', 'foo (custom)', 'custom');
        $catalogue->set('foo', 'foo (foo)', 'foo');

        $translator = new TranslationManager(new IntlMessageFormatter());
        $translator->addMessageCatalogue($catalogue);

        $template = $this->getTemplate($templates, $translator);

        static::assertEquals('foo (foo)foo (custom)foo (foo)foo (custom)foo (foo)foo (custom)', \trim($template->render([])));
    }

    public function testDefaultTranslationDomainWithNamedArguments(): void
    {
        $templates = [
            'index' => '
                {%- trans_default_domain "foo" %}
                {%- block content %}
                    {{- "foo"|trans(parameters = {}, domain = "custom") }}
                    {{- "foo"|trans() }}
                    {{- "foo"|trans(parameters = {}, domain = "custom") }}
                    {{- "foo"|trans({}, domain = "custom") }}
                    {{- "foo"|trans({}, "custom", locale = "fr") }}
                    {{- "foo"|trans(parameters = {}, domain = "custom") }}
                    {{- "foo"|trans({}, "custom", locale = "fr") }}
                {% endblock %}
            ',
            'base' => '
                {%- block content "" %}
            ',
        ];

        $catalogue = new MessageCatalogue('en');
        $catalogue->set('foo', 'foo (messages)');
        $catalogue->set('foo', 'foo (custom)', 'custom');
        $catalogue->set('foo', 'foo (foo)', 'foo');

        $catalogue2 = new MessageCatalogue('fr');
        $catalogue2->set('foo', 'foo (fr)', 'custom');

        $translator = new TranslationManager(new IntlMessageFormatter());
        $translator->addMessageCatalogue($catalogue);
        $translator->addMessageCatalogue($catalogue2);

        $template = $this->getTemplate($templates, $translator);

        static::assertEquals('foo (custom)foo (foo)foo (custom)foo (custom)foo (fr)foo (custom)foo (fr)', \trim($template->render([])));
    }

    /**
     * @param array|string                                           $template
     * @param null|\Viserio\Component\Translation\TranslationManager $translator
     *
     * @return \Twig_Template
     */
    private function getTemplate($template, ?TranslationManager $translator = null)
    {
        if ($translator === null) {
            $translator = $this->getTranslationManager();
        }

        if (\is_array($template)) {
            $loader = new TwigArrayLoader($template);
        } else {
            $loader = new TwigArrayLoader(['index' => $template]);
        }

        $twig = new Environment($loader, ['debug' => true, 'cache' => false]);
        $twig->addExtension(new TranslatorExtension($translator));

        return $twig->loadTemplate('index');
    }

    /**
     * @return \Viserio\Component\Translation\TranslationManager
     */
    private function getTranslationManager(): TranslationManager
    {
        $translator = new TranslationManager(new IntlMessageFormatter());
        $translator->addMessageCatalogue(new MessageCatalogue('en'));
        $translator->addMessageCatalogue(new MessageCatalogue('fr'));

        return $translator;
    }
}
