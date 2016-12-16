<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Util;

use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Cloner\AbstractCloner;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Viserio\Support\Debug\HtmlDumper;

/**
 * Ported.
 * @see https://github.com/filp/whoops/blob/master/src/Whoops/Util/TemplateHelper.php
 */
final class TemplateHelper
{
    /**
     * @var \Viserio\Support\Debug\HtmlDumper
     */
    private static $htmlDumper;

    /**
     * @var AbstractCloner
     */
    private static $cloner;

    /**
     * @var HtmlDumperOutput
     */
    private static $htmlDumperOutput;

    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Format the given value into a human readable string.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function dump($value): string
    {
        $dumper = self::getDumper();

        // re-use the same DumpOutput instance, so it won't re-render the global styles/scripts on each dump.
        // exclude verbose information (e.g. exception stack traces)
        $cloneVar = self::getCloner()->cloneVar($value, Caster::EXCLUDE_VERBOSE);

        $dumper->dump(
            $cloneVar,
            self::$htmlDumperOutput
        );

        $output = self::$htmlDumperOutput->getOutput();

        self::$htmlDumperOutput->flush();

        return $output;
    }

    /**
     * Escapes a string for output in an HTML document
     *
     * @param string $raw
     *
     * @return string
     */
    public static function escape(string $raw): string
    {
        $flags = ENT_QUOTES;

        // HHVM has all constants defined, but only ENT_IGNORE
        // works at the moment
        if (defined('ENT_SUBSTITUTE') && !defined('HHVM_VERSION')) {
            $flags |= ENT_SUBSTITUTE;
        } else {
            // This is for 5.3.
            // The documentation warns of a potential security issue,
            // but it seems it does not apply in our case, because
            // we do not blacklist anything anywhere.
            $flags |= ENT_IGNORE;
        }

        $raw = str_replace(chr(9), '    ', $raw);

        return htmlspecialchars($raw, $flags, 'UTF-8');
    }

    /**
     * Get the cloner used for dumping variables.
     *
     * @return \Symfony\Component\VarDumper\Cloner\AbstractCloner
     */
    protected static function getCloner()
    {
        if (!self::$cloner) {
            self::$cloner = new VarCloner();
        }

        return self::$cloner;
    }

    /**
     * [getDumper description]
     *
     * @return \Viserio\Support\Debug\HtmlDumper
     */
    private static function getDumper(): HtmlDumper
    {
        if (self::$htmlDumper === null) {
            self::$htmlDumperOutput = new HtmlDumperOutput();

            // re-use the same var-dumper instance, so it won't re-render the global styles/scripts on each dump.
            self::$htmlDumper = new HtmlDumper(self::$htmlDumperOutput);
        }

        return self::$htmlDumper;
    }
}
