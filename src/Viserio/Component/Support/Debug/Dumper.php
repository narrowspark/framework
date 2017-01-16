<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Debug;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * @codeCoverageIgnore
 */
class Dumper
{
    /**
     * Var dump a value elegantly.
     *
     * @param mixed $value
     *
     * @return void
     */
    public static function dump($value): void
    {
        if (class_exists(CliDumper::class)) {
            $dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();
            $dumper->dump((new VarCloner())->cloneVar($value));
        } else {
            var_dump($value);
        }
    }
}
