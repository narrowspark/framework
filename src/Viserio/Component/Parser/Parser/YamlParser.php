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

use RuntimeException;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Viserio\Contract\Parser\Exception\ParseException;
use Viserio\Contract\Parser\Parser as ParserContract;
use Viserio\Contract\Support\Exception\MissingPackageException;

class YamlParser implements ParserContract
{
    /**
     * Bit to customize the YAML parser.
     *
     * @var int
     */
    protected $flags = 0;

    /**
     * A Yaml parser instance.
     *
     * @var \Symfony\Component\Yaml\Parser
     */
    private $parser;

    /**
     * Create a new Yaml parser.
     *
     * @throws RuntimeException
     */
    public function __construct()
    {
        /** @codeCoverageIgnoreStart */
        if (! \class_exists(SymfonyYaml::class)) {
            throw new MissingPackageException(['symfony/yaml'], self::class);
        }
        /** @codeCoverageIgnoreEnd */
        $this->parser = new SymfonyYamlParser();
    }

    /**
     * A bit field of PARSE_* constants to customize the YAML parser behavior.
     *
     * @param int $flags
     */
    public function setFlags(int $flags): void
    {
        $this->flags = $flags;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        $payload = \preg_replace('/\t+/', '', $payload);

        if ($payload === null) {
            throw new ParseException('Failed to remove tab characters.');
        }

        try {
            return $this->parser->parse(\trim($payload), $this->flags);
        } catch (YamlParseException $exception) {
            throw ParseException::createFromException($exception->getMessage(), $exception);
        }
    }
}
