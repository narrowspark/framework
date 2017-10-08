<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Dumper;

use Viserio\Component\Contract\Parser\Dumper as DumperContract;
use Viserio\Component\Contract\Parser\Exception\DumpException;

class PoDumper implements DumperContract
{
    /**
     * @var array
     */
    const LINE_ENDINGS    = ['unix' => "\n", 'win' => "\r\n"];

    /**
     * Returns configured line ending (option 'line-ending' ['win', 'unix']).
     *
     * @var string
     */
    private $eol;

    /**
     * PoDumper constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $default = [
            'multiline-glue' => '<##EOL##>', // Token used to separate lines in msgid
            'context-glue'   => '<##EOC##>', // Token used to separate ctxt from msgid
            'line-ending'    => 'unix',
        ];

        $options = array_merge($default, $options);

        $this->eol = self::LINE_ENDINGS[$options['line-ending']];
    }

    /**
     * {@inheritdoc}
     *
     * array[]
     *     ['headers']        array                               (optional)
     *     ['locale']         string Only is needed if the
     *                               Key "headers" is empty.      (optional)
     *     array[]
     *         ['msgid']      array ID of the message.
     *         ['msgstr']     array Message translation.
     *         ['msgctxt']    array Message context.              (optional)
     *         ['tcomment']   array Comment from translator.      (optional)
     *         ['ccomment']   array Extracted comments from code. (optional)
     *         ['references'] array Location of string in code.   (optional)
     *         ['obsolete']   bool  Is the message obsolete?      (optional)
     *         ['fuzzy']      bool  Is the message "fuzzy"?       (optional)
     *         ['flags']      array Flags of the entry.           (optional)
     */
    public function dump(array $data): string
    {
        $output = '';

        if (isset($data['headers']) && count($data['headers']) > 0) {
            $output .= "msgid \"\"" . $this->eol;
            $output .= "msgstr \"\"" . $this->eol;

            foreach ($data['headers'] as $header) {
                $output .= $header . $this->eol;
            }

            unset($data['headers']);

            $output .= $this->eol;
        } else {
            $output = $this->createHeader($data, $output);
        }

        $entriesCount = count($data);
        $counter      = 0;

        foreach ($data as $entry) {
            if (isset($entry['previous'])) {
                foreach ((array) $entry['previous'] as $key => $value) {
                    if (is_string($value)) {
                        $output .= '#| ' .$key. ' ' .$this->cleanExport($value).$this->eol;
                    } elseif (is_array($value) && count($value) > 0) {
                        foreach ($value as $line) {
                            $output .= '#| ' .$key. ' ' .$this->cleanExport($line).$this->eol;
                        }
                    }
                }
            }

            if (isset($entry['tcomment'])) {
                foreach ($entry['tcomment'] as $comment) {
                    $output .= "# ".$comment.$this->eol;
                }
            }

            if (isset($entry['ccomment'])) {
                foreach ($entry['ccomment'] as $comment) {
                    $output .= '#. '.$comment.$this->eol;
                }
            }

            if (isset($entry['reference'])) {
                foreach ($entry['reference'] as $ref) {
                    $output .= '#: '.$ref.$this->eol;
                }
            }

            if (isset($entry['flags']) && !empty($entry['flags'])) {
                $output .= "#, ".implode(', ', $entry['flags']).$this->eol;
            }

            if (isset($entry['@'])) {
                $output .= "#@ ".$entry['@'].$this->eol;
            }

            if (isset($entry['msgctxt'])) {
                $output .= 'msgctxt '.$this->cleanExport($entry['msgctxt'][0]).$this->eol;
            }

            $isObsolete = isset($entry['obsolete']) && $entry['obsolete'];
            $isPlural   = isset($entry['msgid_plural']);

            if ($isObsolete) {
                $output .= "#~ ";
            }

            if (isset($entry['msgid'])) {
                // Special clean for msgid
                if (is_string($entry['msgid'])) {
                    $msgid = explode($this->eol, $entry['msgid']);
                } elseif (is_array($entry['msgid'])) {
                    $msgid = $entry['msgid'];
                } else {
                    throw new \Exception('msgid not string or array');
                }

                $output .= 'msgid ';

                foreach ($msgid as $i => $id) {
                    if ($i > 0 && $isObsolete) {
                        $output .= "#~ ";
                    }
                    $output .= $this->cleanExport($id).$this->eol;
                }
            }

            if (isset($entry['msgid_plural'])) {
                // Special clean for msgid_plural
                if (is_string($entry['msgid_plural'])) {
                    $msgidPlural = explode($this->eol, $entry['msgid_plural']);
                } elseif (is_array($entry['msgid_plural'])) {
                    $msgidPlural = $entry['msgid_plural'];
                } else {
                    throw new \Exception('msgid_plural not string or array');
                }
                $output .= 'msgid_plural ';
                foreach ($msgidPlural as $plural) {
                    $output .= $this->cleanExport($plural).$this->eol;
                }
            }
            
            if (count(preg_grep('/^msgstr/', array_keys($entry)))) { // checks if there is a key starting with msgstr
                if ($isPlural) {
                    $noTranslation = true;
                    foreach ($entry as $key => $value) {
                        if (strpos($key, 'msgstr[') === false) {
                            continue;
                        }
                        $output .= $key." ";
                        $noTranslation = false;
                        foreach ($value as $i => $t) {
                            $output .= $this->cleanExport($t).$this->eol;
                        }
                    }
                    if ($noTranslation) {
                        $output .= 'msgstr[0] '.$this->cleanExport('').$this->eol;
                        $output .= 'msgstr[1] '.$this->cleanExport('').$this->eol;
                    }
                } else {
                    foreach ((array)$entry['msgstr'] as $i => $t) {
                        if ($i == 0) {
                            if ($isObsolete) {
                                $output .= "#~ ";
                            }
                            $output .= 'msgstr '.$this->cleanExport($t).$this->eol;
                        } else {
                            if ($isObsolete) {
                                $output .= "#~ ";
                            }
                            $output .= $this->cleanExport($t).$this->eol;
                        }
                    }
                }
            }
            $counter++;
            // Avoid inserting an extra newline at end of file
            if ($counter < $entriesCount) {
                $output .= $this->eol;
            }
        }
        return $output;
    }

    /**
     * @param array  $data
     * @param string $output
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\DumpException
     *
     * @return string
     */
    private function createHeader(array $data, $output): string
    {
        if (!isset($data['locale']) && $data['locale'] !== '') {
            throw new DumpException('No language key found; Please add [locale] to your data array.');
        }

        $charset = $data['charset'] ?? 'UTF-8';

        $output .= '"Content-Type: text/plain; charset=' . $charset . $this->eol;
        $output .= '"Content-Transfer-Encoding: 8bit\n"' . $this->eol;
        $output .= '"Language: ' . $data['locale'] . $this->eol;
        $output .= $this->eol;

        unset($data['locale'], $data['charset']);

        return $output;
    }

    /**
     * Prepares a string to be outputed into a file.
     *
     * @param string $string The string to be converted.
     *
     * @return string
     */
    protected function cleanExport(string $string): string
    {
        $quote   = '"';
        $slash   = '\\';
        $newline = $this->eol;

        $replaces = [
            "$slash" => "$slash$slash",
            "$quote" => "$slash$quote",
            "\t" => '\t',
        ];

        $string = str_replace(array_keys($replaces), array_values($replaces), $string);
        $po     = $quote.implode("${slash}n$quote$newline$quote", explode($newline, $string)).$quote;

        // remove empty strings
        return str_replace("$newline$quote$quote", '', $po);
    }
}
