<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Viserio\Bridge\Twig\NodeVisitor\TranslationNodeVisitor;
use Viserio\Bridge\Twig\TokenParser\TransTokenParser;
use Viserio\Component\Contracts\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;

class TranslatorExtension extends AbstractExtension
{
    /**
     * Translation instance.
     *
     * @var null|\Viserio\Component\Contracts\Translation\TranslationManager
     */
    protected $translationManager;

    /**
     * A instance of NodeVisitorInterface.
     *
     * @var null|\Twig\NodeVisitor\NodeVisitorInterface|\Viserio\Bridge\Twig\NodeVisitor\TranslationNodeVisitor
     */
    private $translationNodeVisitor;

    /**
     * Create a new translator extension.
     *
     * @param \Viserio\Component\Contracts\Translation\TranslationManager $translationManager
     * @param null|\Twig\NodeVisitor\NodeVisitorInterface                 $translationNodeVisitor
     */
    public function __construct(TranslationManagerContract $translationManager, ?NodeVisitorInterface $translationNodeVisitor = null)
    {
        $this->translationManager = $translationManager;

        if ($translationNodeVisitor === null) {
            $translationNodeVisitor = new TranslationNodeVisitor();
        }

        $this->translationNodeVisitor = $translationNodeVisitor;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Viserio_Bridge_Twig_Extension_Translator';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('trans', [$this, 'trans']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'trans',
                [$this, 'trans'],
                [
                    'pre_escape' => 'html',
                    'is_safe'    => ['html'],
                ]
            ),
        ];
    }

    /**
     * Returns the token parser instance to add to the existing list.
     *
     * @return \Twig\TokenParser\AbstractTokenParser[]
     */
    public function getTokenParsers(): array
    {
        return [
            // {% trans %}Narrowspark is great!{% endtrans %}
            // or
            // {% trans %}
            //     {count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}}
            // {% endtrans %}
            new TransTokenParser(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors(): array
    {
        return [$this->translationNodeVisitor];
    }

    /**
     * Get a translation node visitor instance.
     *
     * @return \Twig\NodeVisitor\NodeVisitorInterface
     */
    public function getTranslationNodeVisitor(): NodeVisitorInterface
    {
        return $this->translationNodeVisitor;
    }

    /**
     * Get a language translator instance.
     *
     * @param null|string $locale
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Component\Contracts\Translation\Translator
     */
    public function getTranslator(?string $locale = null): TranslatorContract
    {
        return $this->translationManager->getTranslator($locale);
    }

    /**
     * Translates the given message.
     *
     * @param string      $id         The message id
     * @param mixed       $parameters An array of parameters for the message
     * @param string      $domain     The domain for the message or null to use the default
     * @param null|string $locale     The locale to change the translator language
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    public function trans(
        string $id,
        $parameters = [],
        string $domain = 'messages',
        ?string $locale = null
    ): string {
        if (\is_numeric($parameters)) {
            $parameters = ['count' => $parameters];
        }

        return $this->translationManager->getTranslator($locale)->trans($id, $parameters, $domain);
    }
}
