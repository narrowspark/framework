<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Dumper;

use Viserio\Component\Contract\Parser\Dumper as DumperContract;
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
