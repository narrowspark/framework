<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extensions;

use Twig_Extension;
use Twig_Filter;
use Viserio\Component\Contracts\Validation\Validator as ValidatorContract;

class ValidatorExtension extends Twig_Extension
{
    /**
     * Create a new validator extension.
     *
     * @param \Viserio\Component\Contracts\Validation\Validator $validator
     */
    public function __construct(ValidatorContract $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Viserio_Bridge_Twig_Extension_Validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new Twig_Filter(
                'passes',
                [$this->validator, 'passes'],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new Twig_Filter(
                'fails',
                [$this->validator, 'fails'],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new Twig_Filter(
                'validate',
                [$this->validator, 'validate'],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
