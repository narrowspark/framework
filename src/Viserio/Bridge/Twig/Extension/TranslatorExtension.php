<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Viserio\Component\Contracts\Translation\Traits\TranslatorAwareTrait;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;

class TranslatorExtension extends AbstractExtension
{
    use TranslatorAwareTrait;

    /**
     * Create a new translator extension.
     *
     * @param \Viserio\Component\Contracts\Translation\Translator $translator
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
        return 'Viserio_Provider_Twig_Extension_Translator';
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
}
