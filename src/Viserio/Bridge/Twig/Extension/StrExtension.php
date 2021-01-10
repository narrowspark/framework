<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Bridge\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Viserio\Component\Support\Str as ViserioStr;

class StrExtension extends AbstractExtension
{
    /** @var string */
    protected $staticClassName = ViserioStr::class;

    /**
     * Return the string object callback.
     */
    public function getStaticClassName(): string
    {
        return $this->staticClassName;
    }

    /**
     * Set a new string callback.
     *
     * @param string $staticClassName
     */
    public function setStaticClassName($staticClassName): void
    {
        $this->staticClassName = $staticClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Viserio_Bridge_Twig_Extension_String';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'str_*',
                function (string $name) {
                    $arguments = \array_slice(\func_get_args(), 1);

                    $name = (string) ViserioStr::camelize($name);

                    return $this->staticClassName::$name(...$arguments);
                }
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'str_*',
                function (string $name) {
                    $arguments = \array_slice(\func_get_args(), 1);
                    $name = (string) ViserioStr::camelize($name);

                    return $this->staticClassName::$name(...$arguments);
                }
            ),
        ];
    }
}
