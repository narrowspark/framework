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

namespace Viserio\Component\Parser\Parser;

use InvalidArgumentException;
use Viserio\Component\Parser\Utils\XmlUtils;
use Viserio\Contract\Parser\Exception\ParseException;
use Viserio\Contract\Parser\Parser as ParserContract;

class XmlParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            $dom = XmlUtils::loadString($payload);
            // Work around to accept xml input

            $data = \json_encode((array) \simplexml_import_dom($dom), \JSON_THROW_ON_ERROR);
            $data = \json_decode($data, true);
            $data = \str_replace([':{}', ':[]'], ':null', $data);
        } catch (InvalidArgumentException $exception) {
            throw ParseException::createFromException($exception->getMessage(), $exception);
        }

        return $data;
    }
}
