<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extensions;

use Twig_Extension;
use Twig_SimpleFilter;

/**
 * Based on the Symfony Twig Bridge Code Extension.
 *
 * Twig extension relate to PHP code and used by the profiler and the default exception templates.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CodeExtension extends Twig_Extension
{
    /**
     * The format for links to source files.
     *
     * @var string
     */
    private $fileLinkFormat;

    /**
     * The project root directory.
     *
     * @var string
     */
    private $rootDir;

    /**
     * The charset.
     *
     * @var string
     */
    private $charset;

    /**
     * Constructor.
     *
     * @param string $fileLinkFormat
     * @param string $rootDir
     * @param string $charset
     */
    public function __construct(string $fileLinkFormat, string $rootDir, string $charset)
    {
        $this->fileLinkFormat = $fileLinkFormat ?: ini_get('xdebug.file_link_format') ?: get_cfg_var('xdebug.file_link_format');
        $this->rootDir        = str_replace('/', DIRECTORY_SEPARATOR, dirname($rootDir)) . DIRECTORY_SEPARATOR;
        $this->charset        = $charset;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('abbr_class', [$this, 'abbrClass'], ['is_safe' => ['html']]),
            new Twig_SimpleFilter('abbr_method', [$this, 'abbrMethod'], ['is_safe' => ['html']]),
            new Twig_SimpleFilter('format_args', [$this, 'formatArgs'], ['is_safe' => ['html']]),
            new Twig_SimpleFilter('format_args_as_text', [$this, 'formatArgsAsText']),
            new Twig_SimpleFilter('file_excerpt', [$this, 'fileExcerpt'], ['is_safe' => ['html']]),
        ];
    }

    public function abbrClass($class)
    {
        $parts = explode('\\', $class);
        $short = array_pop($parts);

        return sprintf('<abbr title="%s">%s</abbr>', $class, $short);
    }

    public function abbrMethod($method)
    {
        if (false !== mb_strpos($method, '::')) {
            list($class, $method) = explode('::', $method, 2);
            $result               = sprintf('%s::%s()', $this->abbrClass($class), $method);
        } elseif ('Closure' === $method) {
            $result = sprintf('<abbr title="%s">%s</abbr>', $method, $method);
        } else {
            $result = sprintf('<abbr title="%s">%s</abbr>()', $method, $method);
        }

        return $result;
    }

    /**
     * Formats an array as a string.
     *
     * @param array $args The argument array
     *
     * @return string
     */
    public function formatArgs($args)
    {
        $result = [];
        foreach ($args as $key => $item) {
            if ('object' === $item[0]) {
                $parts          = explode('\\', $item[1]);
                $short          = array_pop($parts);
                $formattedValue = sprintf('<em>object</em>(<abbr title="%s">%s</abbr>)', $item[1], $short);
            } elseif ('array' === $item[0]) {
                $formattedValue = sprintf('<em>array</em>(%s)', is_array($item[1]) ? $this->formatArgs($item[1]) : $item[1]);
            } elseif ('null' === $item[0]) {
                $formattedValue = '<em>null</em>';
            } elseif ('boolean' === $item[0]) {
                $formattedValue = '<em>' . mb_strtolower(var_export($item[1], true)) . '</em>';
            } elseif ('resource' === $item[0]) {
                $formattedValue = '<em>resource</em>';
            } else {
                $formattedValue = str_replace("\n", '', htmlspecialchars(var_export($item[1], true), ENT_COMPAT | ENT_SUBSTITUTE, $this->charset));
            }

            $result[] = is_int($key) ? $formattedValue : sprintf("'%s' => %s", $key, $formattedValue);
        }

        return implode(', ', $result);
    }

    /**
     * Formats an array as a string.
     *
     * @param array $args The argument array
     *
     * @return string
     */
    public function formatArgsAsText($args)
    {
        return strip_tags($this->formatArgs($args));
    }

    /**
     * Returns an excerpt of a code file around the given line number.
     *
     * @param string $file       A file path
     * @param int    $line       The selected line number
     * @param int    $srcContext The number of displayed lines around or -1 for the whole file
     *
     * @return string An HTML string
     */
    public function fileExcerpt(string $file, int $line, int $srcContext = 3): string
    {
        if (is_readable($file)) {
            // highlight_file could throw warnings
            // see https://bugs.php.net/bug.php?id=25725
            $code = @highlight_file($file, true);
            // remove main code/span tags
            $code = preg_replace('#^<code.*?>\s*<span.*?>(.*)</span>\s*</code>#s', '\\1', $code);
            // split multiline spans
            $code = preg_replace_callback('#<span ([^>]++)>((?:[^<]*+<br \/>)++[^<]*+)</span>#', function ($m) {
                return "<span $m[1]>" . str_replace('<br />', "</span><br /><span $m[1]>", $m[2]) . '</span>';
            }, $code);
            $content = explode('<br />', $code);

            $lines = [];

            if (0 > $srcContext) {
                $srcContext = count($content);
            }

            for ($i = max($line - $srcContext, 1), $max = min($line + $srcContext, count($content)); $i <= $max; ++$i) {
                $lines[] = '<li' . ($i == $line ? ' class="selected"' : '') . '><a class="anchor" name="line' . $i . '"></a><code>' . self::fixCodeMarkup($content[$i - 1]) . '</code></li>';
            }

            return '<ol start="' . max($line - $srcContext, 1) . '">' . implode("\n", $lines) . '</ol>';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Viserio_Bridge_Twig_Extension_Code';
    }

    /**
     * Fix code markup.
     *
     * @param  string $line
     *
     * @return string
     */
    protected static function fixCodeMarkup(string $line): string
    {
        // </span> ending tag from previous line
        $opening = mb_strpos($line, '<span');
        $closing = mb_strpos($line, '</span>');

        if (false !== $closing && (false === $opening || $closing < $opening)) {
            $line = substr_replace($line, '', $closing, 7);
        }

        // missing </span> tag at the end of line
        $opening = mb_strpos($line, '<span');
        $closing = mb_strpos($line, '</span>');

        if (false !== $opening && (false === $closing || $closing > $opening)) {
            $line .= '</span>';
        }

        return $line;
    }
}
