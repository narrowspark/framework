<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Parser;

use Viserio\Component\Contract\Parser\Exception\ParseException;
use Viserio\Component\Contract\Parser\Parser as ParserContract;

class PoParser implements ParserContract
{
    private const DEFAULT_ENTRY = [
        'msgid'      => [],
        'msgstr'     => [],
        'msgctxt'    => [],
        'ccomment'   => [],
        'tcomment'   => [],
        'obsolete'   => false,
        'fuzzy'      => false,
        'flags'      => [],
        'references' => [],
    ];

    /**
     * {@inheritdoc}
     * [
     *     'msgid'      => <string> ID of the message.
     *     'msgctxt'    => <string> Message context.
     *     'msgstr'     => <string> Message translation.
     *     'tcomment'   => <string> Comment from translator.
     *     'ccomment'   => <string> Extracted comments from code.
     *     'references' => <array> Location of string in code.
     *     'obsolete'   => <bool> Is the message obsolete?
     *     'fuzzy'      => <bool> Is the message "fuzzy"?
     *     'flags'      => <array> Flags of the entry. Internal usage.
     * ].
     */
    public function parse(string $payload): array
    {
        $lines = \explode("\n", $payload);
        $i     = 0;

        $entries         = [];
        $entry           = self::DEFAULT_ENTRY;
        $justNewEntry    = false; // A new entries has been just inserted.
        $lastPreviousKey = null; // Used to remember last key in a multiline previous entries.
        $state           = null;
        $firstLine       = true;

        for ($n = \count($lines); $i < $n; ++$i) {
            $line      = \trim($lines[$i]);
            $line      = $this->fixMultiLines($line, $lines, $i);
            $splitLine = \preg_split('/\s+/', $line, 2);
            $key       = $splitLine[0];

            if ($line === '' || ($key === 'msgid' && isset($entry['msgid']))) {
                // Two consecutive blank lines
                if ($justNewEntry) {
                    continue;
                }

                if ($firstLine) {
                    $firstLine = false;

                    continue;
                } else {
                    $entries[] = $entry;
                }

                $state           = null;
                $justNewEntry    = true;
                $lastPreviousKey = null;
                $entry           = self::DEFAULT_ENTRY;

                if ($line === '') {
                    continue;
                }
            }

            $justNewEntry = false;
            $data         = $splitLine[1] ?? '';

            switch ($key) {
                case '#': // # Translator comments
                    $entry['tcomment'][] = self::convertString($data);
                    break;

                case '#.': // #. Comments extracted from source code
                    $entry['ccomment'][] = self::convertString($data);
                    break;

                case '#,': // Flagged translation
                    $entry['flags'] = preg_split('/,\s*/', $data);
                    $entry['fuzzy'] = in_array('fuzzy', $entry['flags'], true);
                    break;

                case '#:':
                    $entry['references'] = self::addReferences($data);
                    break;

                case '#|':  // Previous untranslated string
                case '#~':  // Old entries
                case '#~|': // Previous-Old untranslated string.
                    if ($key === '#|') {
                        $key = 'previous';
                    } elseif ($key === '#~') {
                        $key = 'obsolete';
                    } elseif ($key === '#~|') {
                        $key = 'previous-obsolete';
                    }

                    $tmpParts = explode(' ', $data);
                    $tmpKey   = $tmpParts[0];

                    if (! in_array($tmpKey, ['msgid', 'msgid_plural', 'msgstr', 'msgctxt'], true)) {
                        $tmpKey = $lastPreviousKey; // If there is a multiline previous string we must remember what key was first line.
                        $str    = $data;
                    } else {
                        $str = implode(' ', array_slice($tmpParts, 1));
                    }

                    $entry[$key] = $entry[$key] ?? ['msgid' => [], 'msgstr' => []];

                    if (mb_strpos($key, 'obsolete') !== false) {
                        [$entry, $lastPreviousKey] = self::processObsoleteEntry($lastPreviousKey, $tmpKey, $str, $entry);
                    }

                    if ($key !== 'obsolete') {
                        [$entry, $lastPreviousKey] = self::processPreviousEntry($lastPreviousKey, $tmpKey, $str, $entry, $key);
                    }

                    break;

                case '#@': // ignore #@ default
                    $entry['@'] = self::convertString($data);
                    break;

                // context
                // Allows disambiguations of different messages that have same msgid.
                // Example:
                //
                // #: tools/observinglist.cpp:700
                // msgctxt "First letter in 'Scope'"
                // msgid "S"
                // msgstr ""
                //
                // #: skycomponents/horizoncomponent.cpp:429
                // msgctxt "South"
                // msgid "S"
                // msgstr ""
                case 'msgctxt':
                case 'msgid':        // untranslated-string
                case 'msgid_plural': // untranslated-string-plural
                case 'msgstr':       // translated-string
                    $state           = $key;

                    if ($state === 'msgstr') {
                        $entry[$state][] = self::convertString(trim($data, '"'));
                    } else {
                        $entry[$state][] = self::convertString($data);
                    }

                    break;

                default:
                    if (mb_strpos($key, 'msgstr[') !== false) {
                        // translated-string-case-n
                        $state           = $key;
                        $entry[$state][] = self::convertString($data);
                    } else {
                        // "multiline" lines
                        $entry = self::extractMultiLines($state, $entry, $line, $key, $i);
                    }

                    break;
            }
        }

        if ($state === 'msgstr') {
            $entries[] = $entry;
        }

        // - Cleanup header data
        $headers = [];

        foreach ($entries as $key => $entry) {
            if (count($entry['msgid']) === 0) {
                $headers = self::extractHeaders($entry['msgstr'][0], $headers);

                unset($entries[$key]);
            }
        }

        $entries['headers'] = $headers;

        return $entries;
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
     * @param array  $data
     *
     * @return array
     */
    private static function extractHeaders($headers, array $data): array
    {
        $headerArray   = \explode("\n", $headers);
        $currentHeader = null;

        foreach ($headerArray as $line) {
            $line = self::convertString($line);

            if ($line === '') {
                continue;
            }

            if (self::isHeaderDefinition($line)) {
                $header                         = array_map('trim', explode(':', $line, 2));
                $currentHeader                  = $header[0];
                $data[$currentHeader]           = $header[1];
            } else {
                $entry                          = $data[$currentHeader] ?? '';
                $data[$currentHeader]           = $entry . $line;
            }
        }

        return $data;
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
     * Export reference infos.
     *
     * @param string $data
     *
     * @return array
     */
    private static function addReferences($data): array
    {
        $references = [];

        foreach (\preg_split('/\s+/', \trim($data)) as $value) {
            if (\preg_match('/^(.+)(:(\d*))?$/U', $value, $matches)) {
                $filename = $matches[1];
                $line     = $matches[3] ?? null;
                $key      = sprintf('{%s}:{%s}', $filename, $line);

                $references[$key] = [$filename, $line];
            }
        }

        return $references;
    }

    /**
     * @param null|string $lastPreviousKey
     * @param null|string $tmpKey
     * @param string      $str
     * @param array       $entry
     *
     * @return array
     */
    private static function processObsoleteEntry(
        ?string $lastPreviousKey,
        ?string $tmpKey,
        string $str,
        array $entry
    ): array {
        $entry['obsolete'] = true;

        switch ($tmpKey) {
            case 'msgid':
                $entry['msgid'][] = self::convertString($str);
                $lastPreviousKey  = $tmpKey;

                break;
            case 'msgstr':
                if ($str === '""') {
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
     * @param null|string $lastPreviousKey
     * @param null|string $tmpKey
     * @param string      $str
     * @param array       $entry
     * @param string      $key
     *
     * @return array
     */
    private static function processPreviousEntry(
        ?string $lastPreviousKey,
        ?string $tmpKey,
        string $str,
        array $entry,
        string $key
    ): array {
        switch ($tmpKey) {
            case 'msgid':
            case 'msgid_plural':
            case 'msgstr':
                $entry[$key][$tmpKey][] = self::convertString($str);
                $lastPreviousKey        = $tmpKey;
                break;
            default:
                $entry[$key][$tmpKey] = self::convertString($str);
                break;
        }

        return [$entry, $lastPreviousKey];
    }

    /**
     * @param null|string $state
     * @param array       $entry
     * @param $line
     * @param $key
     * @param int $i
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\ParseException
     *
     * @return array
     */
    private static function extractMultiLines(?string $state, array $entry, $line, $key, int $i): array
    {
        switch ($state) {
            case 'msgctxt':
            case 'msgid':
            case 'msgid_plural':
            case mb_strpos($state, 'msgstr[') !== false:
                if (is_string($entry[$state])) {
                    // Convert it to array
                    $entry[$state] = [$entry[$state]];
                }

                $entry[$state][] = self::convertString($line);
                break;
            case 'msgstr':
                // Special fix where msgid is ""
                if ($entry['msgid'] === '""') {
                    $entry['msgstr'][] = self::convertString(trim($line, '"'));
                } else {
                    $entry['msgstr'][] = self::convertString($line);
                }

                break;
            default:
                throw new ParseException([
                    'message' => sprintf(
                        'Parse error! Unknown key [%s] on line %s',
                        $key,
                        $i
                    ),
                    'line' => $i,
                ]);
        }

        return $entry;
    }
}
