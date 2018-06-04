<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Dumper;

use Narrowspark\PrettyArray\PrettyArray;
use Viserio\Component\Contract\Parser\Dumper as DumperContract;

class PhpDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        return '<?php' . \PHP_EOL . 'declare(strict_types=1);' . \PHP_EOL . \PHP_EOL . 'return ' . PrettyArray::print($data) . ';' . \PHP_EOL;
    }
}
