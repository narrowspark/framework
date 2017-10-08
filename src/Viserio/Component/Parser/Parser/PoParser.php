<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Parser;

use Viserio\Component\Contract\Parser\Parser as ParserContract;

class PoParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        $lines = \explode("\n", $payload);
        $i     = 0;

        $translation  = [];
        $translations = [];

        for ($n = \count($lines); $i < $n; ++$i) {
            $line = \trim($lines[$i]);
            $line = $this->fixMultiLines($line, $lines, $i);

            if ($line === '') {
                if (isset($translations['']) && $translations[''] === '') {
                    $translations = self::extractHeaders($translations[''], $translations);
                } elseif (! isset($translations[''])) {
                    $translations[] = $translation;
                }

                continue;
            }

            $splitLine = \preg_split('/\s+/', $line, 2);
            $key       = $splitLine[0];
            $data      = $splitLine[1] ?? '';

            switch ($key) {
                case '#':
                    $translation->addComment($data);
                    $append = null;
                    break;

                case '#.':
                    $translation->addExtractedComment($data);
                    $append = null;
                    break;

                case '#,':
                    foreach (array_map('trim', \explode(',', \trim($data))) as $value) {
                        $translation->addFlag($value);
                    }
                    $append = null;
                    break;

                case '#:':
                    foreach (\preg_split('/\s+/', \trim($data)) as $value) {
                        if (\preg_match('/^(.+)(:(\d*))?$/U', $value, $matches)) {
                            $translation->addReference($matches[1], isset($matches[3]) ? $matches[3] : null);
                        }
                    }
                    $append = null;
                    break;

                case 'msgctxt':
                    $translation = $translation->getClone(self::convertString($data));
                    $append      = 'Context';
                    break;

                case 'msgid':
                    $translation = $translation->getClone(null, self::convertString($data));
                    $append      = 'Original';
                    break;

                case 'msgid_plural':
                    $translation->setPlural(self::convertString($data));
                    $append = 'Plural';
                    break;

                case 'msgstr':
                case 'msgstr[0]':
                    $translation->setTranslation(self::convertString($data));
                    $append = 'Translation';
                    break;

                case 'msgstr[1]':
                    $translation->setPluralTranslations([self::convertString($data)]);
                    $append = 'PluralTranslation';
                    break;

                default:
                    if (mb_strpos($key, 'msgstr[') === 0) {
                        $p   = $translation->getPluralTranslations();
                        $p[] = self::convertString($data);

                        $translation->setPluralTranslations($p);
                        $append = 'PluralTranslation';
                        break;
                    }

                    if (isset($append)) {
                        if ($append === 'Context') {
                            $translation = $translation->getClone($translation->getContext()
                                . "\n"
                                . self::convertString($data));
                            break;
                        }

                        if ($append === 'Original') {
                            $translation = $translation->getClone(null, $translation->getOriginal()
                                . "\n"
                                . self::convertString($data));
                            break;
                        }

                        if ($append === 'PluralTranslation') {
                            $p   = $translation->getPluralTranslations();
                            $p[] = array_pop($p) . "\n" . self::convertString($data);
                            $translation->setPluralTranslations($p);
                            break;
                        }

                        $getMethod = 'get' . $append;
                        $setMethod = 'set' . $append;
                        $translation->$setMethod($translation->$getMethod() . "\n" . self::convertString($data));
                    }
                    break;
            }
        }

        if ($translation->hasOriginal() && ! \in_array($translation, \iterator_to_array($translations))) {
            $translations[] = $translation;
        }

        return $translations;
    }

    /**
     * Gets one string from multiline strings.
     *
     * @param string $line
     * @param array  $lines
     * @param int    &$i
     *
     * @return string
     */
    private function fixMultiLines(string $line, array $lines, int &$i): string
    {
        for ($j = $i, $t = \count($lines); $j < $t; ++$j) {
            if (isset($lines[$j + 1]) &&
                \mb_substr($line, -1, 1) === '"' &&
                \mb_substr(trim($lines[$j + 1]), 0, 1) === '"'
            ) {
                $line = \mb_substr($line, 0, -1) . \mb_substr(trim($lines[$j + 1]), 1);
            } else {
                $i = $j;
                break;
            }
        }

        return $line;
    }

    /**
     * Convert a string from its PO representation.
     *
     * @param string $value
     *
     * @return string
     */
    private static function convertString(string $value): string
    {
        if (! $value) {
            return '';
        }

        if ($value[0] === '"') {
            $value = \mb_substr($value, 1, -1);
        }

        return \strtr(
            $value,
            [
                '\\\\' => '\\',
                '\\a'  => "\x07",
                '\\b'  => "\x08",
                '\\t'  => "\t",
                '\\n'  => "\n",
                '\\v'  => "\x0b",
                '\\f'  => "\x0c",
                '\\r'  => "\r",
                '\\"'  => '"',
            ]
        );
    }

    /**
     * Add the headers found to the translations instance.
     *
     * @param string $headers
     * @param array  $translations
     *
     * @return array
     */
    private static function extractHeaders($headers, array $translations): array
    {
        $headers       = \explode("\n", $headers);
        $currentHeader = null;

        foreach ($headers as $line) {
            $line = self::convertString($line);

            if ($line === '') {
                continue;
            }

            if (self::isHeaderDefinition($line)) {
                $header                                 = array_map('trim', explode(':', $line, 2));
                $currentHeader                          = $header[0];
                $translations['header'][$currentHeader] = $header[1];
            } else {
                $entry                                  = $translations['header'][$currentHeader] ?? '';
                $translations['header'][$currentHeader] = $entry . $line;
            }
        }

        return $translations;
    }

    /**
     * Checks if it is a header definition line. Useful for distguishing between header definitions
     * and possible continuations of a header entry.
     *
     * @param string $line Line to parse
     *
     * @return bool
     */
    private static function isHeaderDefinition(string $line): bool
    {
        return (bool) preg_match('/^[\w-]+:/', $line);
    }
}
