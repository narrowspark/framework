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

namespace Viserio\Component\Session;

use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use SessionHandlerInterface as SessionHandlerContract;

class EncryptedStore extends Store
{
    /**
     * Encryption key instance.
     *
     * @var \ParagonIE\Halite\Symmetric\EncryptionKey
     */
    private $key;

    /**
     * Create a new session instance.
     */
    public function __construct(string $name, SessionHandlerContract $handler, EncryptionKey $key)
    {
        parent::__construct($name, $handler);

        $this->key = $key;
    }

    /**
     * Hide this from var_dump(), etc.
     */
    public function __debugInfo(): array
    {
        return [
            'key' => 'private',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareForReadFromHandler($data): array
    {
        $hiddenString = Crypto::decrypt($data, $this->key);

        if ($decryptedValue = $hiddenString->getString()) {
            $sessionData = \json_decode($decryptedValue, true);

            \sodium_memzero($decryptedValue);

            if ($sessionData === null) {
                return [];
            }

            return $sessionData;
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareForWriteToHandler(string $data): string
    {
        return Crypto::encrypt(new HiddenString($data), $this->key);
    }
}
