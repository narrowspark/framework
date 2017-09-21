<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Parser;

use Viserio\Component\Contract\Parser\Exception\ParseException;
use Viserio\Component\Contract\Parser\Parser as ParserContract;

class PoParser implements ParserContract
{
    private $entries = [];

    private $headers = [];

    private $options = [
        'multiline-glue' => '<##EOL##>',  // Token used to separate lines in msgid
        'context-glue' => '<##EOC##>',  // Token used to separate ctxt from msgid
        'line-ending' => 'unix',
    ];

    private $lineEndings = ['unix' => "\n", 'win' => "\r\n"];

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        $lines = \explode("\n", $payload);
        $i     = 0;

        $headers = [];
        $hash = [];
        $entry = [];
        $justNewEntry = false; // A new entry has been just inserted.
        $firstLine = true;
        $lastPreviousKey = null; // Used to remember last key in a multiline previous entry.
        $state = null;

        for ($n = \count($lines); $i < $n; ++$i) {
            $line = \trim($lines[$i]);
            $line = $this->fixMultiLines($line, $lines, $i);

            if ($line === '') {
                if (isset($translations['']) && $translations[''] === '') {
                    $translations = self::extractHeaders($translations[''], $translations);
                }

                continue;
            }

            $splitLine = \preg_split('/\s+/', $line, 2);
            $key       = $splitLine[0];
            $data      = $splitLine[1] ?? '';

            switch ($key) {
                case '#':
                    $entry = $this->addComment($data, $entry);
                    break;

                case '#.':
                    $entry = $this->addExtractedComment($data, $entry);
                    break;

                case '#,':
                    $entry = $this->addFlags($data, $entry);
                    break;

                case '#:':
                    $entry = $this->addReference($data, $entry);
                    break;

                case '#|':  // Previous untranslated string
                case '#~':  // Old entry
                case '#~|': // Previous-Old untranslated string.
                    $type = $key;

                    if ($key === '#|') {
                        $type = 'previous';
                    } elseif ($key === '#~') {
                        $type = 'obsolete';
                    } elseif ($key === '#~|') {
                        $type = 'previous-obsolete';
                    }

                $tmpParts = explode(' ', $data);
                $tmpKey = $tmpParts[0];

                if (!in_array($tmpKey, ['msgid', 'msgid_plural', 'msgstr', 'msgctxt'], true)) {
                    $tmpKey = $lastPreviousKey; // If there is a multiline previous string we must remember what key was first line.
                    $str = $data;
                } else {
                    $str = implode(' ', array_slice($tmpParts, 1));
                }

                $entry[$type] = $entry[$type] ?? ['msgid' => [], 'msgstr' => []];

                if ($type === 'obsolete' || $type === 'previous-obsolete') {
                    [$entry, $lastPreviousKey] = $this->addObsoleteEntry($lastPreviousKey, $tmpKey, $str, $entry);
                }

                if ($type === 'previous') {
                    [$entry, $lastPreviousKey] = $this->addPreviousEntry($lastPreviousKey, $tmpKey, $str, $entry, $type);
                }

                break;

                case 'msgctxt':
                case 'msgid':        // untranslated-string
                case 'msgid_plural': // untranslated-string-plural
                    $state = $key;
                    $entry[$state][] = self::convertString($data);
                    break;

                case 'msgstr':
                    $state = 'msgstr';
                    $entry[$state][] = self::convertString($data);
                    break;

                default:
                    if (strpos($key, 'msgstr[') !== false) {
                        // translated-string-case-n
                        $state = $key;
                        $entry[$state][] = self::convertString($data);
                    } else {
                        // "multiline" lines
                        switch ($state) {
                            case 'msgctxt':
                            case 'msgid':
                            case 'msgid_plural':
                            case (strpos($state, 'msgstr[') !== false):
                                if (is_string($entry[$state])) {
                                    // Convert it to array
                                    $entry[$state] = [$entry[$state]];
                                }

                                $entry[$state][] = self::convertString($line);
                                break;
                            case 'msgstr':
                                // Special fix where msgid is ""
                                if ($entry['msgid'] === "\"\"") {
                                    $entry['msgstr'][] = trim($line, '"');
                                } else {
                                    $entry['msgstr'][] = $line;
                                }

                                break;
                            default:
                                throw new ParseException([
                                    'message' => sprintf(
                                        'Parse error! Unknown key [%s] on line %s',
                                        $key,
                                        ($lineNumber + 1)
                                    ),
                                    'line' => $lineNumber
                                ]);
                        }
                    }

                    break;
            }
        }

        return $entry;
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
                \mb_strpos(trim($lines[$j + 1]), '"') === 0
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

    /**
     * Translator comments.
     *
     * @param $data
     * @param array $entry
     *
     * @return array
     */
    private function addComment($data, $entry): array
    {
        if (!in_array($data, $entry['comment'], true)) {
            $entry['comment'][] = self::convertString($data);
        }

        return $entry;
    }

    /**
     * Comments extracted from source code.
     *
     * @param $data
     * @param array $entry
     *
     * @return array
     */
    private function addExtractedComment($data, array $entry): array
    {
        if (!in_array($data, $entry['extractedComment'], true)) {
            $entry['extractedComment'][] = self::convertString($data);
        }

        return $entry;
    }

    /**
     * Flagged translation.
     *
     * @param $data
     * @param array $entry
     *
     * @return array
     */
    private function addFlags($data, array $entry): array
    {
        foreach (array_map('trim', \explode(',', \trim($data))) as $value) {
            if (!in_array($value, $entry['flags'], true)) {
                $entry['flags'][] = self::convertString($value);
            }
        }

        return $entry;
    }

    /**
     * Reference
     *
     * @param $data
     * @param array $entry
     *
     * @return array
     */
    private function addReference($data, $entry): array
    {
        foreach (\preg_split('/\s+/', \trim($data)) as $value) {
            if (\preg_match('/^(.+)(:(\d*))?$/U', $value, $matches)) {
                $filename = $matches[1];
                $line = $matches[3] ?? null;
                $key = sprintf('{%s}:{%s}', $filename, $line);

                $entry['reference'][$key] = [$filename, $line];
            }
        }

        return $entry;
    }

    /**
     * @param $lastPreviousKey
     * @param $tmpKey
     * @param $str
     * @param $entry
     * @return array
     */
    private function addObsoleteEntry($lastPreviousKey, $tmpKey, $str, $entry): array
    {
        $entry['obsolete'] = true;

        switch ($tmpKey) {
            case 'msgid':
                $entry['msgid'][] = self::convertString($str);
                $lastPreviousKey = $tmpKey;
                break;
            case 'msgstr':
                if ($str === "\"\"") {
                    $entry['msgstr'][] = self::convertString(trim($str, '"'));
                } else {
                    $entry['msgstr'][] = self::convertString($str);
                }

                $lastPreviousKey = $tmpKey;
                break;
            default:
                break;
        }

        return [$entry, $lastPreviousKey];
    }

    /**
     * @param $lastPreviousKey
     * @param $tmpKey
     * @param $str
     * @param $entry
     * @param $type
     *
     * @return array
     */
    private function addPreviousEntry($lastPreviousKey, $tmpKey, $str, $entry, $type): array
    {
        switch ($tmpKey) {
            case 'msgid':
            case 'msgid_plural':
            case 'msgstr':
                $entry[$type][$tmpKey][] = self::convertString($str);
                $lastPreviousKey = $tmpKey;
                break;
            default:
                $entry[$type][$tmpKey] = self::convertString(($str);
                break;
        }

        return [$entry, $lastPreviousKey];
    }
}
