<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use Viserio\Component\Contracts\Encryption\HiddenString as HiddenStringContract;

final class HiddenString implements HiddenStringContract
{
    /**
     * @var string
     */
    private $internalStringValue;

    /**
     * Create a new  HiddenString.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->internalStringValue = safe_str_cpy($value);
    }

    /**
     * Wipe it from memory after it's been used.
     */
    public function __destruct()
    {
        \sodium_memzero($this->internalStringValue);
    }

    /**
     * Hide its internal state from var_dump().
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'internalStringValue' => '*',
            'attention'           => 'If you need the value of a HiddenString, ' .
                'invoke getString() instead of dumping it.',
        ];
    }

    /**
     * Disallow the contents from being accessed via __toString().
     *
     * @return string
     */
    public function __toString(): string
    {
        return '';
    }

    /**
     * Disallow the contents from being accessed via __sleep().
     *
     * @return array
     */
    public function __sleep(): array
    {
        return [];
    }

    /**
     * Explicit invocation -- get the raw string value.
     *
     * @return string
     */
    public function getString(): string
    {
        return safe_str_cpy($this->internalStringValue);
    }
}
