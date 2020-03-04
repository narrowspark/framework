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

namespace Viserio\Component\Parser\Parser;

use Viserio\Contract\Parser\Exception\ParseException;
use Viserio\Contract\Parser\Parser as ParserContract;
use Viserio\Contract\Support\Exception\MissingPackageException;
use Yosymfony\Toml\Exception\ParseException as TomlParseException;
use Yosymfony\Toml\Toml as YosymfonyToml;

class TomlParser implements ParserContract
{
    /**
     * Create a new Toml parser.
     *
     * @throws \Viserio\Contract\Support\Exception\MissingPackageException
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        if (! \class_exists(YosymfonyToml::class)) {
            throw new MissingPackageException(['yosymfony/toml'], self::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            return YosymfonyToml::parse($payload);
        } catch (TomlParseException $exception) {
            throw ParseException::createFromException('Unable to parse the TOML string.', $exception);
        }
    }
}
