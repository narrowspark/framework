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
     *
     * @return string
     */
    public function getStaticClassName(): string
    {
        return $this->staticClassName;
    }

    /**
     * Set a new string callback.
     *
     * @param string $staticClassName
     *
     * @return void
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
