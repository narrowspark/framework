<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Extractor;

use Viserio\Component\Contract\Translation\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Translation\Extractor as ExtractorContract;

class ChainExtractor implements ExtractorContract
{
    /**
     * The extractors.
     *
     * @var \Viserio\Component\Contract\Translation\Extractor[]
     */
    private $extractors = [];

    /**
     * Adds a loader to the translation extractor.
     *
     * @param string                                            $format
     * @param \Viserio\Component\Contract\Translation\Extractor $extractor
     *
     * @return void
     */
    public function addExtractor(string $format, ExtractorContract $extractor): void
    {
        $this->extractors[$format] = $extractor;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix(string $prefix): void
    {
        foreach ($this->extractors as $extractor) {
            $extractor->setPrefix($prefix);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function extract($resource): array
    {
        if (! \is_string($resource) && ! \is_array($resource)) {
            throw new InvalidArgumentException(\sprintf(
                'The resource parameter must be of type string or array, [%s] given.',
                \is_object($resource) ? \get_class($resource) : \gettype($resource)
            ));
        }

        $messages = [];

        foreach ($this->extractors as $extractor) {
            foreach ($extractor->extract($resource) as $key => $foundMessages) {
                $messages[$key] = $foundMessages;
            }
        }

        return $messages;
    }
}
