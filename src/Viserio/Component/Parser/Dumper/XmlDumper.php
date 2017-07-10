<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Dumper;

use DOMException;
use RuntimeException;
use Spatie\ArrayToXml\ArrayToXml;
use Viserio\Component\Contract\Parser\Dumper as DumperContract;
use Viserio\Component\Contract\Parser\Exception\DumpException;

class XmlDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        // @codeCoverageIgnoreStart
        if (! \class_exists(ArrayToXml::class)) {
            throw new RuntimeException('Unable to dump XML, the ArrayToXml dumper is not installed.');
        }
        // @codeCoverageIgnoreEnd

        try {
            return ArrayToXml::convert($data);
        } catch (DOMException $exception) {
            throw new DumpException($exception->getMessage());
        }
    }
}
