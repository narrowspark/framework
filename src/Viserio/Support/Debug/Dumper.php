<?php
namespace Viserio\Support\Debug;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * Dumper.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class Dumper
{
    /**
     * Var dump a value elegantly.
     *
     * @param mixed $value
     *
     * @return string|null
     */
    public function dump($value)
    {
        if (class_exists(CliDumper::class)) {
            $dumper = 'cli' === PHP_SAPI ? new CliDumper : new HtmlDumper;
            $dumper->dump((new VarCloner)->cloneVar($value));
        } else {
            var_dump($value);
        }
    }
}
