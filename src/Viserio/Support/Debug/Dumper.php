<?php
namespace Viserio\Support\Debug;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * Dumper.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
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
        $cloner = new VarCloner();
        $dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();
        $dumper->dump($cloner->cloneVar($value));
    }
}
