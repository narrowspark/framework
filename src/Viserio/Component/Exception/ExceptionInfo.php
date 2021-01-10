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

namespace Viserio\Component\Exception;

use Narrowspark\HttpStatus\Exception\InvalidArgumentException;
use Narrowspark\HttpStatus\Exception\OutOfBoundsException;
use Narrowspark\HttpStatus\HttpStatus;

final class ExceptionInfo
{
    public static function generate(string $id, int $code): array
    {
        try {
            $info = [
                'id' => $id,
                'code' => $code,
                'name' => HttpStatus::getReasonPhrase($code),
                'detail' => HttpStatus::getReasonMessage($code),
            ];
        } catch (InvalidArgumentException | OutOfBoundsException $error) {
            $info = [
                'id' => $id,
                'code' => 500,
                'name' => HttpStatus::getReasonPhrase(500),
                'detail' => HttpStatus::getReasonMessage(500),
            ];
        }

        return $info;
    }
}
