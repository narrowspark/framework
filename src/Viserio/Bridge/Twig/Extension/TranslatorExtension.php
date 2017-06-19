<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Viserio\Bridge\Twig\NodeVisitor\TranslationNodeVisitor;
use Viserio\Bridge\Twig\TokenParser\TransTokenParser;
use Viserio\Component\Contracts\Translation\Traits\TranslatorAwareTrait;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;

class TranslatorExtension extends AbstractExtension
{
    use TranslatorAwareTrait;

    /**
     * A instance of NodeVisitorInterface.
     *
     * @var \Twig\NodeVisitor\NodeVisitorInterface|null
     */
    private $translationNodeVisitor;

    /**
     * Create a new translator extension.
     *
     * @param \Viserio\Component\Contracts\Translation\Translator $translator
     * @param \Twig\NodeVisitor\NodeVisitorInterface|null         $translationNodeVisitor
     */
    public function __construct(TranslatorContract $translator, ?NodeVisitorInterface $translationNodeVisitor = null)
    {
        $this->translator = $translator;

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
            new TwigFunction('trans', [$this->translator, 'trans']),
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
                [$this->translator, 'trans'],
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
            // {% trans count %}
            //     {0} There is no apples|{1} There is one apple|]1,Inf] There is {{ count }} apples
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
}
