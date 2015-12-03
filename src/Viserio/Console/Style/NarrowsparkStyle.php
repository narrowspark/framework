<?php
namespace Viserio\Console\Style;

use Symfony\Component\Console\Style\SymfonyStyle;

class NarrowsparkStyle extends SymfonyStyle
{
    /**
     * Formats an error result bar.
     *
     * @param string|array $message
     */
    public function error($message)
    {
        $this->block($message, null, 'error', ' ', false);
    }
}
