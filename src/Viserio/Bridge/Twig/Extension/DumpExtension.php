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

use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Template;
use Twig\TwigFunction;
use Viserio\Bridge\Twig\Exception\RuntimeException;
use Viserio\Bridge\Twig\TokenParser\DumpTokenParser;

/**
 * Dump a variable or the view context.
 *
 * Based on the Symfony Twig Bridge Dump Extension
 *
 * @see https://github.com/symfony/symfony/blob/2.6/src/Symfony/Bridge/Twig/Extension/DumpExtension.php
 *
 * @author Nicolas Grekas <p@tchwork.com>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 */
class DumpExtension extends AbstractExtension
{
    /**
     * A cloner instance.
     *
     * @var \Symfony\Component\VarDumper\Cloner\ClonerInterface
     */
    private $cloner;

    /**
     * A dumper instance.
     *
     * @var \Symfony\Component\VarDumper\Dumper\HtmlDumper
     */
    private $dumper;

    /**
     * DumpExtension constructor.
     */
    public function __construct(ClonerInterface $cloner, HtmlDumper $dumper)
    {
        $this->cloner = $cloner;
        $this->dumper = $dumper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'dump',
                [$this, 'dump'],
                [
                    'is_safe' => ['html'],
                    'needs_context' => true,
                    'needs_environment' => true,
                ]
            ),
        ];
    }

    public function getTokenParsers(): array
    {
        return [new DumpTokenParser()];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Viserio_Bridge_Twig_Extension_Dump';
    }

    public function dump(Environment $env, array $context): string
    {
        if (! $env->isDebug()) {
            return '';
        }

        if (\func_num_args() === 2) {
            $vars = [];

            foreach ($context as $key => $value) {
                if (! $value instanceof Template) {
                    $vars[$key] = $value;
                }
            }

            $vars = [$vars];
        } else {
            $vars = \func_get_args();

            unset($vars[0], $vars[1]);
        }

        $dump = \fopen('php://memory', 'r+b');

        if ($dump === false) {
            throw new RuntimeException('Error opening stream.');
        }

        $this->dumper->setCharset($env->getCharset());

        foreach ($vars as $value) {
            $this->dumper->dump($this->cloner->cloneVar($value), $dump);
        }

        return (string) \stream_get_contents($dump, -1, 0);
    }
}
