<?php
namespace Viserio\Filesystem;

use Viserio\Contracts\Filesystem\TaggableParser as TaggableParserContract;

class TaggableParser implements TaggableParserContract
{
    /**
     * All parser.
     *
     * @var TaggableParserContract
     */
    protected $parser;

    /**
     * Create a new taggable parser.
     *
     * @param ParserContract $parser
     */
    public function __construct(ParserContract $parser)
    {
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($filename, $taggedKey)
    {
        $data = $this->parser->parse($filename);

        return $this->isGroup($taggedKey, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($filename)
    {
        return $this->parser->supports($filename);
    }

    /**
     * Format a json file for saving.
     *
     * @param array $data data
     *
     * @return string data export
     */
    public function dump(array $data)
    {
        return $this->parser->dump($data);
    }

    /**
     * Check if config belongs to a group.
     *
     * @param string|array $taggedKey
     * @param array        $data
     *
     * @return array
     */
    protected function isGroup($taggedKey, array $data)
    {
        $taggedData = [];

        foreach ($data as $key => $value) {
            $name             = sprintf(
                '%s' . TaggableParserContract::TAG_DELIMITER . '%s',
                $group,
                $key
            );

            $taggedData[$name] = $value;
        }

        return $taggedData;
    }
}
