<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use Viserio\Contracts\Translation\Traits\TranslatorAwareTrait;
use Viserio\Contracts\Translation\Translator as TranslatorContract;

class Translator extends Twig_Extension
{
    use TranslatorAwareTrait;

    /**
     * Create a new translator extension.
     *
     * @param \Viserio\Contracts\Translation\Translator
     */
    public function __construct(TranslatorContract $translator)
    {
        $this->translator = $translator;
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
            new Twig_SimpleFunction('trans', [$this->translator, 'trans']),
            new Twig_SimpleFunction('trans_choice', [$this->translator, 'transChoice']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new Twig_SimpleFilter(
                'trans',
                [$this->translator, 'trans'],
                [
                    'pre_escape' => 'html',
                    'is_safe'    => ['html'],
                ]
            ),
            new Twig_SimpleFilter(
                'trans_choice',
                [$this->translator, 'transChoice'],
                [
                    'pre_escape' => 'html',
                    'is_safe'    => ['html'],
                ]
            ),
        ];
    }
}
