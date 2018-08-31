<?php
declare(strict_types=1);
namespace Viserio\Component\Translation;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Viserio\Component\Contract\Translation\MessageCatalogue as MessageCatalogueContract;
use Viserio\Component\Contract\Translation\MessageFormatter as MessageFormatterContract;
use Viserio\Component\Contract\Translation\Translator as TranslatorContract;
use Viserio\Component\Translation\Traits\ValidateLocaleTrait;

class Translator implements TranslatorContract, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use ValidateLocaleTrait;

    /**
     * Formatter instance.
     *
     * @var \Viserio\Component\Contract\Translation\MessageFormatter
     */
    protected $formatter;

    /**
     * The message catalogue.
     *
     * @var \Viserio\Component\Contract\Translation\MessageCatalogue
     */
    protected $catalogue;

    /**
     * All registered filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * All registered helpers.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Default language to translate into. (e.g. 'en').
     *
     * @var string
     */
    protected $locale;

    /**
     * All collected massages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Create new Translator instance.
     *
     * @param \Viserio\Component\Contract\Translation\MessageCatalogue $catalogue
     * @param \Viserio\Component\Contract\Translation\MessageFormatter $formatter
     *
     * @throws \InvalidArgumentException If a locale contains invalid characters
     */
    public function __construct(MessageCatalogueContract $catalogue, MessageFormatterContract $formatter)
    {
        $this->setLocale($catalogue->getLocale());

        $this->catalogue = $catalogue;
        $this->formatter = $formatter;
        $this->logger    = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(): MessageFormatterContract
    {
        return $this->formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogue(): MessageCatalogueContract
    {
        return $this->catalogue;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): TranslatorContract
    {
        self::assertValidLocale($locale);
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function trans(string $id, array $parameters = [], string $domain = 'messages'): string
    {
        if (\preg_match('/^(.*?)(\\[.*?\\])$/', $id, $match) === 1) {
            $id = $match[1];
        }

        $trans = $this->formatter->format(
            $this->catalogue->get($id, $domain),
            $this->locale,
            $parameters
        );
        // Add filter and helper back
        if (isset($match[2])) {
            $trans = $trans . $match[2];
        }

        $trans = $this->applyFilters($trans);
        $trans = $this->applyHelpers($trans);

        $this->log($id, $domain);
        $this->collectMessage($this->locale, $domain, $id, $trans, $parameters);

        return $trans;
    }

    /**
     * {@inheritdoc}
     */
    public function addHelper(string $name, callable $helper): TranslatorContract
    {
        $this->helpers[$name] = $helper;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(callable $filter): TranslatorContract
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Get all collected messages.
     *
     * @return array
     */
    public function getCollectedMessages(): array
    {
        return $this->messages;
    }

    /**
     * Apply helpers.
     *
     * @param string $translation
     *
     * @return mixed
     */
    protected function applyHelpers(string $translation)
    {
        $helpers = $this->filterHelpersFromString($translation);

        if (\count($this->helpers) === 0 || \count($helpers) === 0) {
            return $translation;
        }

        foreach ($helpers as $helper) {
            if (! isset($this->helpers[$helper['name']])) {
                return $translation;
            }

            \array_unshift($helper['arguments'], $translation);

            $translation = \call_user_func_array($this->helpers[$helper['name']], $helper['arguments']);
        }

        return $translation;
    }

    /**
     * Filter a helper from string.
     *
     * @param string $translation
     *
     * @return array
     */
    protected function filterHelpersFromString(string $translation): array
    {
        $helpers = [];

        if (\preg_match('/^(.*?)\\[(.*?)\\]$/', $translation, $match) === 1) {
            $helpers = \explode('|', $match[2]);
            $helpers = \array_map(function ($helper) {
                $name = $helper;
                $arguments = [];

                if (\preg_match('/^(.*?)\:(.*)$/', $helper, $match) === 1) {
                    $name = $match[1];
                    $arguments = \explode(':', $match[2]);
                }

                return [
                    'name'      => $name,
                    'arguments' => $arguments,
                ];
            }, $helpers);
        }

        return $helpers;
    }

    /**
     * Apply filter on string.
     *
     * @param string $translation
     *
     * @return string
     */
    protected function applyFilters(string $translation): string
    {
        if (\count($this->filters) === 0) {
            return $translation;
        }

        foreach ($this->filters as $filter) {
            $translation = $filter($translation);
        }

        return $translation;
    }

    /**
     * Logs for missing translations.
     *
     * @param string $id
     * @param string $domain
     *
     * @return void
     */
    protected function log(string $id, string $domain): void
    {
        $catalogue = $this->catalogue;

        if ($catalogue->defines($id, $domain)) {
            return;
        }

        if ($catalogue->has($id, $domain)) {
            $this->logger->debug(
                'Translation use fallback catalogue.',
                ['id' => $id, 'domain' => $domain, 'locale' => $catalogue->getLocale()]
            );
        } else {
            $this->logger->warning(
                'Translation not found.',
                ['id' => $id, 'domain' => $domain, 'locale' => $catalogue->getLocale()]
            );
        }
    }

    /**
     * Collect messages about all translations.
     *
     * @param null|string $locale
     * @param string      $domain
     * @param string      $id
     * @param string      $translation
     * @param array       $parameters
     *
     * @return void
     */
    protected function collectMessage(
        ?string $locale,
        string $domain,
        string $id,
        string $translation,
        array $parameters = []
    ): void {
        $catalogue = $this->catalogue;

        if ($catalogue->defines($id, $domain)) {
            $state = self::MESSAGE_DEFINED;
        } elseif ($catalogue->has($id, $domain)) {
            $state             = self::MESSAGE_EQUALS_FALLBACK;
            $fallbackCatalogue = $catalogue->getFallbackCatalogue();

            while ($fallbackCatalogue) {
                if ($fallbackCatalogue->defines($id, $domain)) {
                    $locale = $fallbackCatalogue->getLocale();

                    break;
                }

                $fallbackCatalogue = $fallbackCatalogue->getFallbackCatalogue();
            }
        } else {
            $state = self::MESSAGE_MISSING;
        }

        $this->messages[] = [
            'locale'      => $locale,
            'domain'      => $domain,
            'id'          => $id,
            'translation' => $translation,
            'parameters'  => $parameters,
            'state'       => $state,
        ];
    }
}
