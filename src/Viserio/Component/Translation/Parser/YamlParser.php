<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Parser;

use Symfony\Component\Yaml\Yaml;
use Viserio\Component\Parser\Parser\YamlParser as BaseYamlParser;

class YamlParser extends BaseYamlParser
{
    /**
     * {@inheritdoc}
     */
    protected $flags = Yaml::PARSE_CONSTANT;

    /**
     * {@inheritdoc}
     */
    public function setFlags(int $flags): void
    {
        // cant be changed
    }
}
