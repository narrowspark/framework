<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

final class HiddenString
{
    /**
     * @var string
     */
    private $internalStringValue = '';

    /**
     * Disallow the contents from being accessed via __toString()?
     *
     * @var bool
     */
    private $disallowInline = false;

    /**
     * Disallow the contents from being accessed via __sleep()?
     *
     * @var bool
     */
    private $disallowSerialization = false;

    /**
     * HiddenString constructor.
     *
     * @param string $value
     * @param bool   $disallowInline
     * @param bool   $disallowSerialization
     */
    public function __construct(
        string $value,
        bool $disallowInline = false,
        bool $disallowSerialization = false
    ) {
        $this->internalStringValue   = str_cpy($value);
        $this->disallowInline        = $disallowInline;
        $this->disallowSerialization = $disallowSerialization;
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
     * Returns a copy of the string's internal value, which should be zeroed.
     * Optionally, it can return an empty string.
     *
     * @return string
     */
    public function __toString(): string
    {
        if (! $this->disallowInline) {
            return str_cpy($this->internalStringValue);
        }

        return '';
    }

    /**
     * @return array
     */
    public function __sleep(): array
    {
        if (! $this->disallowSerialization) {
            return [
                'internalStringValue',
                'disallowInline',
                'disallowSerialization',
            ];
        }

        return [];
    }

    /**
     * Explicit invocation -- get the raw string value.
     *
     * @return string
     */
    public function getString(): string
    {
        return str_cpy($this->internalStringValue);
    }
}
