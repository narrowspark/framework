<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Debug;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * @codeCoverageIgnore
 */
final class Dumper
{
    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Var dump a value elegantly.
     *
     * @param mixed $value
     *
     * @return void
     */
    public static function dump($value): void
    {
        if (\class_exists(CliDumper::class)) {
            $dumper = \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true) ? new CliDumper() : new HtmlDumper();
            $dumper->dump((new VarCloner())->cloneVar($value));
        } else {
            \var_dump($value);
        }
    }
}
