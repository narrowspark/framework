<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
