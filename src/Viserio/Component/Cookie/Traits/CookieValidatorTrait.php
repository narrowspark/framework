<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Traits;

use InvalidArgumentException;

trait CookieValidatorTrait
{
    /**
     * Validates the name attribute.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @link http://tools.ietf.org/search/rfc2616#section-2.2
     *
     * @return void
     */
    protected function validateName(string $name): void
    {
        if (mb_strlen($name) < 1) {
            throw new InvalidArgumentException('The name cannot be empty');
        }

        // Name attribute is a token as per spec in RFC 2616
        if (preg_match('/[\x00-\x20\x22\x28-\x29\x2c\x2f\x3a-\x40\x5b-\x5d\x7b\x7d\x7f]/', $name)) {
            throw new InvalidArgumentException(sprintf('The cookie name [%s] contains invalid characters.', $name));
        }
    }

    /**
     * Validates a value.
     *
     * @param string|null $value
     *
     * @throws \InvalidArgumentException
     *
     * @link http://tools.ietf.org/html/rfc6265#section-4.1.1
     *
     * @return void
     */
    protected function validateValue(?string $value = null): void
    {
        if (isset($value)) {
            if (preg_match('/[^\x21\x23-\x2B\x2D-\x3A\x3C-\x5B\x5D-\x7E]/', $value)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'The cookie value [%s] contains invalid characters.',
                        $value
                    )
                );
            }
        }
    }
}
