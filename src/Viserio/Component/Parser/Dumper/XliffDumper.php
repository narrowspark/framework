<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Dumper;

use DOMDocument;
use Viserio\Component\Contract\Parser\Dumper as DumperContract;
use Viserio\Component\Contract\Parser\Exception\DumpException;

/**
 * Some of this code has been ported from Symfony. The original
 * code is (c) Fabien Potencier <fabien@symfony.com>.
 *
 * Good article about xliff @link http://www.wikiwand.com/en/XLIFF
 */
class XliffDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     *
     * For xliff v1:
     *    array[]
     *        ['version']                    string Need to be 1.2
     *        ['source-language']            string
     *        ['target-language']            string
     *        ['encoding']                   string A optional option; to set the file encoding
     *        array['resname']               string
     *            ['source']                 string
     *            ['target']                 string
     *            ['id']                     string A optional option; if this is missing, md5 hash from resname is generated
     *            array['target-attributes']        A optional array to set the target attributes; simple key value array
     *            array['notes']                    A optional array to generate notes
     *                ['content']            string
     *                ['from']               string optional
     *                ['priority']           string optional
     * For xliff v2:
     *    array[]
     *        ['version']                    string Need to be 2.0
     *        ['srcLang']                    string
     *        ['trgLang']                    string
     *        ['encoding']                   string A optional option; to set the file encoding
     *        array['notes']                        A optional array to generate notes
     *            ['content']                string
     *            ['from']                   string optional
     *            ['priority']               string optional
     *        array['id']                    string
     *            ['source']                 string
     *            ['target']                 string
     *            array['target-attributes'] array  A optional array to set the target attributes; simple key value array
     */
    public function dump(array $data): string
    {
        $version = $data['version'];

        unset($data['version']);

        if ($version === '1.2') {
            return self::dumpXliffVersion1($data);
        }

        if ($version === '2.0') {
            return self::dumpXliffVersion2($data);
        }

        throw new DumpException(\sprintf('No support implemented for dumping XLIFF version [%s].', $version));
    }

    /**
     * Dump xliff version 1.
     *
     * @param array $data
     *
     * @return string
     */
    private static function dumpXliffVersion1(array $data): string
    {
        $sourceLanguage = $data['source-language'];
        $targetLanguage = $data['target-language'];
        $encoding       = 'UTF-8';

        if (isset($data['encoding'])) {
            $encoding = $data['encoding'];

            unset($data['encoding']);
        }

        unset($data['source-language'], $data['target-language']);

        $dom               = new DOMDocument('1.0', $encoding);
        $dom->formatOutput = true;

        $xliff = $dom->appendChild($dom->createElement('xliff'));
        $xliff->setAttribute('version', '1.2');
        $xliff->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:1.2');

        $xliffFile = $xliff->appendChild($dom->createElement('file'));
        $xliffFile->setAttribute('source-language', \str_replace('_', '-', $sourceLanguage));

        if ($targetLanguage !== '') {
            $xliffFile->setAttribute('target-language', \str_replace('_', '-', $targetLanguage));
        }

        $xliffFile->setAttribute('datatype', 'plaintext');
        $xliffFile->setAttribute('original', 'file.ext');

        $xliffBody = $xliffFile->appendChild($dom->createElement('body'));

        foreach ($data as $resname => $translation) {
            $unit = $dom->createElement('trans-unit');
            $unit->setAttribute('id', $translation['id'] ?? \md5($resname));
            $unit->setAttribute('resname', $resname);

            $source = $unit->appendChild($dom->createElement('source'));
            $source->appendChild($dom->createTextNode($translation['source']));

            $targetElement = $dom->createElement('target');

            if (isset($translation['target-attributes'])) {
                foreach ($translation['target-attributes'] as $key => $value) {
                    $targetElement->setAttribute($key, $value);
                }
            }

            $target = $unit->appendChild($targetElement);

            // Does the target contain characters requiring a CDATA section?
            if (\preg_match('/[&<>]/', $translation['target']) === 1) {
                $target->appendChild($dom->createCDATASection($translation['target']));
            } else {
                $target->appendChild($dom->createTextNode($translation['target']));
            }

            if (isset($translation['notes'])) {
                foreach ($translation['notes'] as $note) {
                    $noteElement = $dom->createElement('note');
                    $noteElement->appendChild($dom->createTextNode($note['content'] ?? ''));

                    unset($note['content']);

                    foreach ((array) $note as $name => $value) {
                        $noteElement->setAttribute($name, $value);
                    }

                    $unit->appendChild($noteElement);
                }
            }

            $xliffBody->appendChild($unit);
        }

        return $dom->saveXML();
    }

    /**
     * Dump xliff version 2.
     *
     * @param array $data
     *
     * @return string
     */
    private static function dumpXliffVersion2(array $data): string
    {
        $sourceLanguage = $data['srcLang'];
        $targetLanguage = $data['trgLang'];
        $encoding       = 'UTF-8';

        if (isset($data['encoding'])) {
            $encoding = $data['encoding'];

            unset($data['encoding']);
        }

        unset($data['srcLang'], $data['trgLang']);

        $dom               = new DOMDocument('1.0', $encoding);
        $dom->formatOutput = true;

        $xliff = $dom->appendChild($dom->createElement('xliff'));
        $xliff->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:2.0');
        $xliff->setAttribute('version', '2.0');
        $xliff->setAttribute('srcLang', \str_replace('_', '-', $sourceLanguage));
        $xliff->setAttribute('trgLang', \str_replace('_', '-', $targetLanguage));

        $xliffFile = $xliff->appendChild($dom->createElement('file'));
        $xliffFile->setAttribute(
            'id',
            'translation_' . \mb_strtolower(\str_replace('-', '_', $sourceLanguage . '_to_' . $targetLanguage))
        );

        foreach ($data as $id => $translation) {
            $unit = $dom->createElement('unit');
            $unit->setAttribute('id', $id);

            if (isset($translation['notes'])) {
                $notesElement = $dom->createElement('notes');

                foreach ((array) $translation['notes'] as $note) {
                    $noteElement = $dom->createElement('note');
                    $noteElement->appendChild($dom->createTextNode($note['content'] ?? ''));

                    unset($note['content']);

                    foreach ((array) $note as $name => $value) {
                        $noteElement->setAttribute($name, $value);
                    }

                    $notesElement->appendChild($noteElement);
                }

                $unit->appendChild($notesElement);
            }

            $segmentElement = $unit->appendChild($dom->createElement('segment'));
            $source         = $segmentElement->appendChild($dom->createElement('source'));
            $source->appendChild($dom->createTextNode($translation['source']));

            $targetElement = $dom->createElement('target');

            if (isset($translation['target-attributes'])) {
                foreach ($translation['target-attributes'] as $key => $value) {
                    $targetElement->setAttribute($key, $value);
                }
            }

            $target = $segmentElement->appendChild($targetElement);

            // Does the target contain characters requiring a CDATA section?
            if (\preg_match('/[&<>]/', $translation['target']) === 1) {
                $target->appendChild($dom->createCDATASection($translation['target']));
            } else {
                $target->appendChild($dom->createTextNode($translation['target']));
            }

            $xliffFile->appendChild($unit);
        }

        return $dom->saveXML();
    }
}
