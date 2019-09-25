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

use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Viserio\Contract\Parser\Exception\ParseException;
use Viserio\Contract\Parser\Exception\RuntimeException;
use Viserio\Contract\Parser\Parser as ParserContract;

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
     * @throws \RuntimeException
     */
    public function __construct()
    {
        /** @codeCoverageIgnoreStart */
        if (! \class_exists(SymfonyYaml::class)) {
            throw new RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
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
        try {
            return $this->parser->parse(\trim(\preg_replace('/\t+/', '', $payload)), $this->flags);
        } catch (YamlParseException $exception) {
            throw new ParseException(['message' => $exception->getMessage(), 'exception' => $exception]);
        }
    }
}
