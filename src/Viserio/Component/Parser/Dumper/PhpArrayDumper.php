<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Dumper;

use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Contract\Parser\Dumper as DumperContract;

class PhpArrayDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        return '<?php' . \PHP_EOL . 'declare(strict_types=1);' . \PHP_EOL . \PHP_EOL . 'return ' . VarExporter::export($data) . ';' . \PHP_EOL;
    }
}
