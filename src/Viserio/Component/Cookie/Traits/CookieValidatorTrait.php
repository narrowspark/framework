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

namespace Viserio\Component\Cookie\Traits;

use Viserio\Contract\Cookie\Exception\InvalidArgumentException;

trait CookieValidatorTrait
{
    /**
     * Validates the name attribute.
     *
     * @throws \Viserio\Contract\Cookie\Exception\InvalidArgumentException
     *
     * @see http://tools.ietf.org/search/rfc2616#section-2.2
     */
    protected function validateName(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('The name cannot be empty.');
        }

        // Name attribute is a token as per spec in RFC 2616
        if (\preg_match('/[\x00-\x20\x22\x28-\x29\x2c\x2f\x3a-\x40\x5b-\x5d\x7b\x7d\x7f]/', $name)) {
            throw new InvalidArgumentException(\sprintf('Cookie name [%s] must not contain invalid characters: ASCII Control characters (0-31;127), space, tab and the following characters: ()<>@,;:\"/[]?={}.', $name));
        }
    }

    /**
     * Validates a value.
     *
     * @throws \Viserio\Contract\Cookie\Exception\InvalidArgumentException
     *
     * @see http://tools.ietf.org/html/rfc6265#section-4.1.1
     */
    protected function validateValue(?string $value = null): void
    {
        if ($value !== null && \preg_match('/[^\x21\x23-\x2B\x2D-\x3A\x3C-\x5B\x5D-\x7E]/', $value)) {
            throw new InvalidArgumentException(\sprintf('The cookie value [%s] contains invalid characters.', $value));
        }
    }
}
