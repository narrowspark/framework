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
        return '<?php
declare(strict_types=1);

return ' . PrettyArray::print($data) . ';';
    }
}
