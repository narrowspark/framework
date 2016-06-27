<?php
namespace Viserio\Translation;

use Countable;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Viserio\Contracts\Translation\{
    MessageCatalogue as MessageCatalogueContract,
    MessageSelector as MessageSelectorContract,
    Translator as TranslatorContract
};
use Viserio\Translation\Traits\ValidateLocaleTrait;

class Translator implements TranslatorContract
{
    use ValidateLocaleTrait;

    /**
     * All registred filters.
     *
     * @var array
     */
    private $filters = [];

    /**
     * All registred helpers.
     *
     * @var array
     */
    private $helpers = [];

    /**
     * Default language to translate into. (e.g. 'en').
     *
     * @var string
     */
    private $locale;

    /**
     * The message selector.
     *
     * @var \Viserio\Contracts\Translation\MessageSelector
     */
    protected $selector;

    /**
     * The message catalogue.
     *
     * @var \Viserio\Contracts\Translation\MessageCatalogue
     */
    protected $catalogue;

    /**
     * The psr logger instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Creat new Translator instance.
     *
     * @param \Viserio\Contracts\Translation\MessageCatalogue $catalogue
     * @param \Viserio\Contracts\Translation\MessageSelector  $selector The message selector for pluralization
     *
     * @throws \InvalidArgumentException If a locale contains invalid characters
     */
    public function __construct(MessageCatalogueContract $catalogue, MessageSelectorContract $selector)
    {
        $this->setLocale($catalogue->getLocale());

        $this->catalogue = $catalogue;
        $this->selector = $selector;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): TranslatorContract
    {
        $this->assertValidLocale($locale);
        $this->locale = $locale;

        return $this;
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
    public function getSelector(): MessageSelectorContract
    {
        return $this->selector;
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
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * {@inheritdoc}
     */
    public function trans(
        string $id,
        array $parameters = [],
        string $domain = 'messages'
    ): string {
        $trans = strtr($this->catalogue->get($id, $domain), $parameters);

        $trans = $this->applyFilters($trans);
        $trans = $this->applyHelpers($trans);

        if ($this->logger !== null) {
            $this->log($id, $domain);
        }

        return $trans;
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice(
        string $id,
        $number,
        array $parameters = [],
        string $domain = 'messages'
    ): string {
        if (is_array($number) || $number instanceof Countable) {
            $number = count($number);
        }

        if (preg_match("/^(.*?)\[(.*?)\]$/", $id, $match)) {
            $id = $match[1];
        }

        $trans = strtr(
            $this->selector->choose(
                $this->catalogue->get($id, $domain),
                $number,
                $this->locale
            ),
            $parameters
        );

        $trans = $this->applyFilters($trans);
        $trans = $this->applyHelpers(empty($match) ? $trans : $trans . '[' . $match[2] . ']');

        if ($this->logger !== null) {
            $this->log($id, $domain);
        }

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
     * Apply helpers.
     *
     * @param string $translation
     *
     * @return mixed
     */
    private function applyHelpers(string $translation)
    {
        $helpers = $this->filterHelpersFromString($translation);

        if (count($this->helpers) === 0 || count($helpers) === 0) {
            return $translation;
        }

        foreach ($helpers as $helper) {
            if (! isset($this->helpers[$helper['name']])) {
                return $translation;
            }

            array_unshift($helper['arguments'], $translation);

            $translation = call_user_func_array($this->helpers[$helper['name']], $helper['arguments']);
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
    private function filterHelpersFromString(string $translation): array
    {
        $helpers = [];

        if (preg_match("/^(.*?)\[(.*?)\]$/", $translation, $match)) {
            $translation = $match[1];
            $helpers = explode('|', $match[2]);
            $helpers = array_map(function ($helper) {
                $name = $helper;
                $arguments = [];

                if (preg_match('/^(.*?)\:(.*)$/', $helper, $match)) {
                    $name = $match[1];
                    $arguments = explode(':', $match[2]);
                }

                return [
                    'name' => $name,
                    'arguments' => $arguments,
                ];
            }, $helpers);
        }

        return $helpers;
    }

    /**
     * Applay filter on string.
     *
     * @param string $translation
     *
     * @return string
     */
    private function applyFilters(string $translation): string
    {
        if (empty($this->filters)) {
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
     */
    private function log(string $id, string $domain)
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
}
