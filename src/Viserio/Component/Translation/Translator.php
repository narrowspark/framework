<?php
declare(strict_types=1);
namespace Viserio\Component\Translation;

use Countable;
use Psr\Log\LoggerAwareInterface;
use Viserio\Component\Contracts\Log\Traits\LoggerAwareTrait;
use Viserio\Component\Contracts\Translation\MessageCatalogue as MessageCatalogueContract;
use Viserio\Component\Contracts\Translation\MessageSelector as MessageSelectorContract;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\Translation\Traits\ValidateLocaleTrait;

class Translator implements TranslatorContract, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use ValidateLocaleTrait;

    /**
     * The message selector.
     *
     * @var \Viserio\Component\Contracts\Translation\MessageSelector
     */
    protected $selector;

    /**
     * The message catalogue.
     *
     * @var \Viserio\Component\Contracts\Translation\MessageCatalogue
     */
    protected $catalogue;

    /**
     * All registred filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * All registred helpers.
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
     * Creat new Translator instance.
     *
     * @param \Viserio\Component\Contracts\Translation\MessageCatalogue $catalogue
     * @param \Viserio\Component\Contracts\Translation\MessageSelector  $selector  The message selector for pluralization
     *
     * @throws \InvalidArgumentException If a locale contains invalid characters
     */
    public function __construct(MessageCatalogueContract $catalogue, MessageSelectorContract $selector)
    {
        $this->setLocale($catalogue->getLocale());

        $this->catalogue = $catalogue;
        $this->selector  = $selector;
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

        $this->collectMessage($this->locale, $domain, $id, $trans, $parameters);

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

        $this->collectMessage($this->locale, $domain, $id, $trans, $parameters, $number);

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
    protected function filterHelpersFromString(string $translation): array
    {
        $helpers = [];

        if (preg_match("/^(.*?)\[(.*?)\]$/", $translation, $match)) {
            $translation = $match[1];
            $helpers     = explode('|', $match[2]);
            $helpers     = array_map(function ($helper) {
                $name = $helper;
                $arguments = [];

                if (preg_match('/^(.*?)\:(.*)$/', $helper, $match)) {
                    $name = $match[1];
                    $arguments = explode(':', $match[2]);
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
     * Applay filter on string.
     *
     * @param string $translation
     *
     * @return string
     */
    protected function applyFilters(string $translation): string
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
    protected function log(string $id, string $domain)
    {
        $catalogue = $this->catalogue;

        if ($catalogue->defines($id, $domain)) {
            return;
        }

        if ($catalogue->has($id, $domain)) {
            $this->getLogger()->debug(
                'Translation use fallback catalogue.',
                ['id' => $id, 'domain' => $domain, 'locale' => $catalogue->getLocale()]
            );
        } else {
            $this->getLogger()->warning(
                'Translation not found.',
                ['id' => $id, 'domain' => $domain, 'locale' => $catalogue->getLocale()]
            );
        }
    }

    /**
     * Collect messages about all translations.
     *
     * @param string|null $locale
     * @param string|null $domain
     * @param string      $id
     * @param string      $translation
     * @param array       $parameters
     * @param int|null    $number
     */
    protected function collectMessage(
        $locale,
        $domain,
        string $id,
        string $translation,
        array $parameters = [],
        int $number = null
    ) {
        if ($domain === null) {
            $domain = 'messages';
        }

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
            'locale'            => $locale,
            'domain'            => $domain,
            'id'                => $id,
            'translation'       => $translation,
            'parameters'        => $parameters,
            'transChoiceNumber' => $number,
            'state'             => $state,
        ];
    }
}
