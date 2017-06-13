<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Dumpers;

use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Support\Traits\ArrayPrettyPrintTrait;

class PhpDumper implements DumperContract
{
    use ArrayPrettyPrintTrait;

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        $output = '<?php
declare(strict_types=1);

return ' . $this->getPrettyPrintArray($data) . ';';

        return $output;
    }
}
