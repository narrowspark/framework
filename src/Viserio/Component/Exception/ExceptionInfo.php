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
