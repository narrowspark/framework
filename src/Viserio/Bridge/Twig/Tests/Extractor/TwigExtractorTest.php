<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extractor;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ReflectionMethod;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\ArrayLoader;
use Twig\Loader\LoaderInterface;
use Viserio\Bridge\Twig\Extension\TranslatorExtension;
use Viserio\Bridge\Twig\Extractor\TwigExtractor;
use Viserio\Component\Contract\Translation\TranslationManager as TranslationManagerContract;

/**
 * @internal
 */
final class TwigExtractorTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Bridge\Twig\Extension\TranslatorExtension
     */
    private $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new TranslatorExtension($this->mock(TranslationManagerContract::class));
    }

    /**
     * @dataProvider extractDataProvider
     *
     * @param mixed $template
     * @param mixed $messages
     */
    public function testExtract($template, $messages): void
    {
        $loader = $this->mock(LoaderInterface::class);

        $twig   = new Environment(
            $loader,
            [
                'strict_variables' => true,
                'debug'            => true,
                'cache'            => false,
                'autoescape'       => false,
            ]
        );

        $twig->addExtension($this->extension);

        $extractor = new TwigExtractor($twig);
        $extractor->setPrefix('prefix');

        $m = new ReflectionMethod($extractor, 'extractTemplate');
        $m->setAccessible(true);
        $array = $m->invoke($extractor, $template);

        foreach ($messages as $key => $domain) {
            $this->assertTrue(isset($array[$domain][$key]));
            $this->assertEquals('prefix' . $key, $array[$domain][$key]);
        }
    }

    public function extractDataProvider(): array
    {
        return [
            ['{{ "new key" | trans() }}', ['new key' => 'messages']],
            ['{{ "new key" | trans() | upper }}', ['new key' => 'messages']],
            ['{{ "new key" | trans({}, "domain") }}', ['new key' => 'domain']],
            ['{% trans %}new key{% endtrans %}', ['new key' => 'messages']],
            ['{% trans %}  new key  {% endtrans %}', ['new key' => 'messages']],
            ['{% trans from "domain" %}new key{% endtrans %}', ['new key' => 'domain']],
            ['{% set foo = "new key" | trans %}', ['new key' => 'messages']],
            ['{{ 1 ? "new key" | trans : "another key" | trans }}', ['new key' => 'messages', 'another key' => 'messages']],
            // make sure 'trans_default_domain' tag is supported
            ['{% trans_default_domain "domain" %}{{ "new key"|trans }}', ['new key' => 'domain']],
            ['{% trans_default_domain "domain" %}{% trans %}new key{% endtrans %}', ['new key' => 'domain']],

            // make sure this works with twig's named arguments
            ['{{ "new key" | trans(domain="domain") }}', ['new key' => 'domain']],
        ];
    }

    /**
     * @dataProvider resourcesWithSyntaxErrorsProvider
     *
     * @param mixed  $resources
     * @param string $dir
     */
    public function testExtractSyntaxError($resources, string $dir): void
    {
        $this->expectException(\Twig\Error\Error::class);

        $extractor = $this->getTwigExtractor();

        try {
            $extractor->extract($resources);
        } catch (Error $exception) {
            $this->assertSame(\str_replace('/', \DIRECTORY_SEPARATOR, $dir . 'syntax_error.twig'), $exception->getFile());
            $this->assertSame(1, $exception->getLine());
            $this->assertSame('Unclosed comment.', $exception->getMessage());

            throw $exception;
        }
    }

    /**
     * @return array
     */
    public function resourcesWithSyntaxErrorsProvider(): array
    {
        return [
            [\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Extractor' . \DIRECTORY_SEPARATOR . 'syntax_error.twig', \dirname(__DIR__) . '' . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Extractor' . \DIRECTORY_SEPARATOR],
            [\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'ErrorExtractor' . \DIRECTORY_SEPARATOR, \dirname(__DIR__) . '' . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'ErrorExtractor' . \DIRECTORY_SEPARATOR],
            [new \SplFileInfo(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Extractor' . \DIRECTORY_SEPARATOR . 'syntax_error.twig'), \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Extractor' . \DIRECTORY_SEPARATOR],
        ];
    }

    /**
     * @dataProvider resourceProvider
     *
     * @param mixed $resource
     */
    public function testExtractWithFiles($resource): void
    {
        $twig = new Environment(
            new ArrayLoader([]),
            [
                'strict_variables' => true,
                'debug'            => true,
                'cache'            => false,
                'autoescape'       => false,
            ]
        );
        $twig->addExtension($this->extension);

        $extractor = new TwigExtractor($twig);
        $array     = $extractor->extract($resource);

        $this->assertTrue(isset($array['messages']['Hi!']));
        $this->assertEquals('Hi!', $array['messages']['Hi!']);
    }

    /**
     * @return array
     */
    public function resourceProvider(): array
    {
        $directory = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Extractor' . \DIRECTORY_SEPARATOR;

        return [
            [$directory . 'with_translations.html.twig'],
            [[$directory . 'with_translations.html.twig']],
            [[new \SplFileInfo($directory . 'with_translations.html.twig')]],
            [new \ArrayObject([$directory . 'with_translations.html.twig'])],
            [new \ArrayObject([new \SplFileInfo($directory . 'with_translations.html.twig')])],
        ];
    }

    /**
     * @return TwigExtractor
     */
    private function getTwigExtractor(): TwigExtractor
    {
        $twig = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock());
        $twig->addExtension($this->extension);

        return new TwigExtractor($twig);
    }
}
