<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Dumpers;

use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;

class PhpDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        $data = var_export($data, true);

        $formatted = str_replace(
            ['  ', '['],
            ['', '['],
            $data
        );

        $output = '<?php
declare(strict_types=1); return ' . $formatted . ';';

        return $output;
    }
}
