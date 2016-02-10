<?php
namespace Viserio\Parsers;

use League\Flysystem\FileNotFoundException;
use RuntimeException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Viserio\Contracts\Filesystem\Exception\LoadingException;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\Filesystem\Parser as ParserContract;

class YamlParser implements ParserContract
{
    /**
     * The filesystem instance.
     *
     * @var \Viserio\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The filesystem instance.
     *
     * @var \Symfony\Component\Yaml\Parser
     */
    protected $parser;

    /**
     * Create a new file filesystem loader.
     *
     * @param \Viserio\Contracts\Filesystem\Filesystem $files
     */
    public function __construct(FilesystemContract $files)
    {
        if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
            throw new RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
        }

        $this->files  = $files;
        $this->parser = new Parser();
    }

    /**
     * {@inheritdoc}
     */
    public function parse($filename)
    {
        if ($this->files->has($filename)) {
            try {
                $data = $this->parser->parse($this->files->read($filename));

                return (array) $data;
            } catch (ParseException $exception) {
                throw new LoadingException(sprintf('Unable to parse the YAML string: [%s]', $exception->getMessage()));
            }
        }

        throw new FileNotFoundException($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($filename)
    {
        return (bool) preg_match('/(\.ya?ml)(\.dist)?/', $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        return $this->parser->dump($data);
    }
}
