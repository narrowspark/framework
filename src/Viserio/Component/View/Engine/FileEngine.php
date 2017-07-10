<?php
declare(strict_types=1);
namespace Viserio\Component\View\Engine;

use Viserio\Component\Contract\View\Engine as EngineContract;

class FileEngine implements EngineContract
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function get(array $fileInfo, array $data = []): string
    {
        return \file_get_contents($fileInfo['path']);
    }
}
