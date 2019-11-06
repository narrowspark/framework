<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Bridge\Twig\Extension;

use InvalidArgumentException;
use RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Viserio\Bridge\Twig\NodeVisitor\TranslationDefaultDomainNodeVisitor;
use Viserio\Bridge\Twig\NodeVisitor\TranslationNodeVisitor;
use Viserio\Bridge\Twig\TokenParser\TransDefaultDomainTokenParser;
use Viserio\Bridge\Twig\TokenParser\TransTokenParser;
use Viserio\Contract\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Contract\Translation\Translator as TranslatorContract;

class TranslatorExtension extends AbstractExtension
{
    /**
     * Translation instance.
     *
     * @var null|\Viserio\Contract\Translation\TranslationManager
     */
    protected $translationManager;

    /**
     * A instance of NodeVisitorInterface.
     *
     * @var \Twig\NodeVisitor\NodeVisitorInterface|\Viserio\Bridge\Twig\NodeVisitor\TranslationNodeVisitor
     */
    private $translationNodeVisitor;

    /**
     * Create a new translator extension.
     *
     * @param \Viserio\Contract\Translation\TranslationManager                                                    $translationManager
     * @param null|\Twig\NodeVisitor\NodeVisitorInterface|\Viserio\Bridge\Twig\NodeVisitor\TranslationNodeVisitor $translationNodeVisitor
     */
    public function __construct(
        TranslationManagerContract $translationManager,
        ?NodeVisitorInterface $translationNodeVisitor = null
    ) {
        $this->translationManager = $translationManager;

        if ($translationNodeVisitor === null) {
            $translationNodeVisitor = new TranslationNodeVisitor();
        }

        $this->translationNodeVisitor = $translationNodeVisitor;
    }

    /**
     * Get a translation node visitor instance.
     *
     * @return \Twig\NodeVisitor\NodeVisitorInterface|\Viserio\Bridge\Twig\NodeVisitor\TranslationNodeVisitor
     */
    public function getTranslationNodeVisitor()
    {
        return $this->translationNodeVisitor;
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
                    'is_safe' => ['html'],
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
            // {% trans_default_domain "foobar" %}
            new TransDefaultDomainTokenParser(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors(): array
    {
        return [$this->translationNodeVisitor, new TranslationDefaultDomainNodeVisitor()];
    }

    /**
     * Get a language translator instance.
     *
     * @param null|string $locale
     *
     * @throws RuntimeException
     *
     * @return \Viserio\Contract\Translation\Translator
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
     * @throws InvalidArgumentException If the locale contains invalid characters
     * @throws RuntimeException         If no translator found
     *
     * @return string The translated string
     */
    public function trans(string $id, $parameters = [], string $domain = 'messages', ?string $locale = null): string
    {
        if (\is_numeric($parameters)) {
            $parameters = ['count' => $parameters];
        }

        return $this->translationManager->getTranslator($locale)->trans($id, $parameters, $domain);
    }
}
