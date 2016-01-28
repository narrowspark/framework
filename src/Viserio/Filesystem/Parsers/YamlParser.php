<?php
namespace Viserio\Filesystem\Parser;

use RuntimeException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Viserio\Contracts\Filesystem\Exception\LoadingException;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Filesystem\Filesystem;
use Viserio\Filesystem\Parser\Traits\IsGroupTrait;

class Yaml implements ParserContract
{
    use IsGroupTrait;

    /**
     * The filesystem instance.
     *
     * @var \Viserio\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new file filesystem loader.
     *
     * @param \Viserio\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Loads a YAML file and gets its' contents as an array.
     *
     * @param string      $filename
     * @param string|null $group
     *
     * @throws \Viserio\Contracts\Filesystem\Exception\LoadingException|\RuntimeException
     *
     * @return array|string|null
     */
    public function load($filename, $group = null)
    {
        if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
            throw new RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
        }

        try {
            if ($this->files->exists($filename)) {
                $data = (new Parser())->parse($this->files->get($filename));

                if ($group !== null) {
                    return $this->isGroup($group, (array) $data);
                }

                return $data;
            }
        } catch (ParseException $exception) {
            throw new LoadingException(sprintf('Unable to parse the YAML string: [%s]', $exception->getMessage()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($filename)
    {
        return (bool) preg_match('#\.ya?ml(\.dist)?$#', $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        return YamlParser::dump($data);
    }
}
