<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extensions;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Twig_Environment;
use Twig_Extension;
use Twig_Function;
use Twig_Template;
use Viserio\Bridge\Twig\TokenParsers\DumpTokenParser;

/**
 * Dump a variable or the view context.
 *
 * Based on the Symfony Twig Bridge Dump Extension
 *
 * @see https://github.com/symfony/symfony/blob/2.6/src/Symfony/Bridge/Twig/Extension/DumpExtension.php
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class DumpExtension extends Twig_Extension
{
    public function __construct()
    {
        $this->cloner = new VarCloner();
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_Function(
                'dump',
                [$this, 'dump'],
                [
                    'is_safe'           => ['html'],
                    'needs_context'     => true,
                    'needs_environment' => true,
                ]
            ),
        ];
    }

    public function getTokenParsers()
    {
        return [new DumpTokenParser()];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Viserio_Bridge_Twig_Extension_Dump';
    }

    /**
     * {inheritdoc}.
     */
    public function dump(Twig_Environment $env, $context)
    {
        if (! $env->isDebug()) {
            return;
        }

        if (func_num_args() === 2) {
            $vars = [];

            foreach ($context as $key => $value) {
                if (! $value instanceof Twig_Template) {
                    $vars[$key] = $value;
                }
            }

            $vars = [$vars];
        } else {
            $vars = func_get_args();

            unset($vars[0], $vars[1]);
        }

        $dump   = fopen('php://memory', 'r+b');
        $dumper = new HtmlDumper($dump);
        $dumper->setCharset($env->getCharset());

        foreach ($vars as $value) {
            $dumper->dump($this->cloner->cloneVar($value));
        }

        rewind($dump);

        return stream_get_contents($dump);
    }
}
