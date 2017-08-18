<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption;

interface Security
{
    // For key derivation security levels:
    public const INTERACTIVE = 'interactive';

    public const MODERATE    = 'moderate';

    public const SENSITIVE   = 'sensitive';
}
