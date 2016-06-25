<?php
namespace Viserio\Parsers\Formats;

use Exception;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;
use Viserio\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2010, Union of RAD http://union-of-rad.org (http://lithify.me/)
 */
class Mo implements FormatContract
{
    /**
     * Magic used for validating the format of a MO file as well as
     * detecting if the machine used to create that file was little endian.
     *
     * @var float
     */
    const MO_LITTLE_ENDIAN_MAGIC = 0x950412de;

    /**
     * Magic used for validating the format of a MO file as well as
     * detecting if the machine used to create that file was big endian.
     *
     * @var float
     */
    const MO_BIG_ENDIAN_MAGIC = 0xde120495;

    /**
     * The size of the header of a MO file in bytes.
     *
     * @var int Number of bytes.
     */
    const MO_HEADER_SIZE = 28;

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (! file_exists($payload)) {
            throw new ParseException([
                'message' => 'File not found.',
            ]);
        }

        $stream = fopen($payload, 'r');
        $stat = fstat($stream);

        if ($stat['size'] < self::MO_HEADER_SIZE) {
            throw new ParseException([
                'message' => 'Failed To Parse the Mo string',
            ]);
        }

        $magic = unpack('V1', fread($stream, 4));
        $magic = hexdec(substr(dechex(current($magic)), -8));

        if ($magic == self::MO_LITTLE_ENDIAN_MAGIC) {
            $isBigEndian = false;
        } elseif ($magic == self::MO_BIG_ENDIAN_MAGIC) {
            $isBigEndian = true;
        } else {
            throw new ParseException([
                'message' => 'MO stream content has an invalid format',
            ]);
        }

        // formatRevision
        $this->readLong($stream, $isBigEndian);
        $count = $this->readLong($stream, $isBigEndian);
        $offsetId = $this->readLong($stream, $isBigEndian);
        $offsetTranslated = $this->readLong($stream, $isBigEndian);
        // sizeHashes
        $this->readLong($stream, $isBigEndian);
        // offsetHashes
        $this->readLong($stream, $isBigEndian);

        $messages = [];

        for ($i = 0; $i < $count; ++$i) {
            $singularId = $pluralId = null;
            $translated = null;

            fseek($stream, $offsetId + $i * 8);

            $length = $this->readLong($stream, $isBigEndian);
            $offset = $this->readLong($stream, $isBigEndian);

            if ($length < 1) {
                continue;
            }

            fseek($stream, $offset);

            $singularId = fread($stream, $length);

            if (strpos($singularId, "\000") !== false) {
                list($singularId, $pluralId) = explode("\000", $singularId);
            }

            fseek($stream, $offsetTranslated + $i * 8);

            $length = $this->readLong($stream, $isBigEndian);
            $offset = $this->readLong($stream, $isBigEndian);

            if ($length < 1) {
                continue;
            }

            fseek($stream, $offset);

            $translated = fread($stream, $length);

            if (strpos($translated, "\000") !== false) {
                $translated = explode("\000", $translated);
            }

            $ids = ['singular' => $singularId, 'plural' => $pluralId];
            $item = compact('ids', 'translated');

            if (is_array($item['translated'])) {
                $messages[$item['ids']['singular']] = stripcslashes($item['translated'][0]);

                if (isset($item['ids']['plural'])) {
                    $plurals = [];

                    foreach ($item['translated'] as $plural => $translated) {
                        $plurals[] = sprintf('{%d} %s', $plural, $translated);
                    }

                    $messages[$item['ids']['plural']] = stripcslashes(implode('|', $plurals));
                }
            } elseif (!empty($item['ids']['singular'])) {
                $messages[$item['ids']['singular']] = stripcslashes($item['translated']);
            }
        }

        fclose($stream);

        return array_filter($messages);
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        $output = $sources = $targets = $sourceOffsets = $targetOffsets = '';
        $offsets = array();
        $size = 0;

        foreach ($data as $source => $target) {
            $offsets[] = array_map('strlen', array($sources, $source, $targets, $target));
            $sources .= "\0".$source;
            $targets .= "\0".$target;
            ++$size;
        }

        $header = array(
            'magicNumber' => self::MO_LITTLE_ENDIAN_MAGIC,
            'formatRevision' => 0,
            'count' => $size,
            'offsetId' => self::MO_HEADER_SIZE,
            'offsetTranslated' => self::MO_HEADER_SIZE + (8 * $size),
            'sizeHashes' => 0,
            'offsetHashes' => self::MO_HEADER_SIZE + (16 * $size),
        );

        $sourcesSize = strlen($sources);
        $sourcesStart = $header['offsetHashes'] + 1;

        foreach ($offsets as $offset) {
            $sourceOffsets .= $this->writeLong($offset[1])
                          .$this->writeLong($offset[0] + $sourcesStart);
            $targetOffsets .= $this->writeLong($offset[3])
                          .$this->writeLong($offset[2] + $sourcesStart + $sourcesSize);
        }

        $output = implode(array_map(array($this, 'writeLong'), $header))
               .$sourceOffsets
               .$targetOffsets
               .$sources
               .$targets;

        return $output;
    }

    /**
     * Reads an unsigned long from stream respecting endianess.
     *
     * @param resource $stream
     * @param bool     $isBigEndian
     *
     * @return int
     */
    private function readLong($stream, bool $isBigEndian): int
    {
        $result = unpack($isBigEndian ? 'N1' : 'V1', fread($stream, 4));
        $result = current($result);

        return (int) substr($result, -8);
    }

    /**
     * @param string $str
     *
     * @return string
     */
    private function writeLong(string $str): string
    {
        return pack('V*', $str);
    }
}
