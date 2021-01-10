<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Parser\Dumper;

use Symfony\Component\VarExporter\VarExporter;
use Viserio\Contract\Parser\Dumper as DumperContract;

class PhpArrayDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        $exportedData = VarExporter::export($data);

        return "<?php\ndeclare(strict_types=1);\n\nreturn {$exportedData};\n";
    }
}
