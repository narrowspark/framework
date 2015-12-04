<?php
namespace Viserio\Translator;

use Viserio\Contracts\Translator\MessageCatalogue as MessageCatalogueContract;

class MessageCatalogue implements MessageCatalogueContract
{
    /**
     * Messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Message catalogue instance.
     *
     * @var MessageCatalogueContract
     */
    protected $fallbackCatalogue;

    /**
     * A string dictating the default language to translate into. (e.g. 'en').
     *
     * @var string
     */
    protected $locale = 'en';

    /**
     *  Parent.
     *
     * @var MessageCatalogueContract
     */
    protected $parent;

    /**
     * Constructor.
     *
     * @param string $locale   The locale
     * @param array  $messages An array of messages classified by domain
     */
    public function __construct($locale, array $messages = [])
    {
        $this->locale = $locale;

        if (isset($messages['lang'])) {
            unset($messages['lang']);
        }

        $this->messages = $messages;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getDomains()
    {
        return array_keys($this->messages);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function all($domain = null)
    {
        if (null === $domain) {
            return $this->messages;
        }

        return isset($this->messages[$domain]) ? $this->messages[$domain] : [];
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function set($id, $translation, $domain = 'messages')
    {
        $this->add([$id => $translation], $domain);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function has($id, $domain = 'messages')
    {
        if (isset($this->messages[$domain][$id])) {
            return true;
        }

        if (null !== $this->fallbackCatalogue) {
            return $this->fallbackCatalogue->has($id, $domain);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function defines($id, $domain = 'messages')
    {
        return isset($this->messages[$domain][$id]);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function get($id, $domain = 'messages')
    {
        if (isset($this->messages[$domain][$id])) {
            return $this->messages[$domain][$id];
        }

        if (null !== $this->fallbackCatalogue) {
            return $this->fallbackCatalogue->get($id, $domain);
        }

        return $id;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function replace($messages, $domain = 'messages')
    {
        $this->messages[$domain] = [];
        $this->add($messages, $domain);
    }

    /**
     * Removes a record.
     *
     * @param string|array $messages
     * @param string       $domain
     */
    public function remove($messages, $domain = 'messages')
    {
        unset($this->messages[$domain][$messages]);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function add($messages, $domain = 'messages')
    {
        if (!isset($this->messages[$domain])) {
            $this->messages[$domain] = $messages;
        } else {
            $this->messages[$domain] = array_replace($this->messages[$domain], $messages);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function addCatalogue(MessageCatalogueContract $catalogue)
    {
        if ($catalogue->getLocale() !== $this->locale) {
            throw new \LogicException(sprintf('Cannot add a catalogue for locale "%s" as the current locale for this catalogue is "%s"', $catalogue->getLocale(), $this->locale));
        }

        foreach ($catalogue->all() as $domain => $messages) {
            $this->add($messages, $domain);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function addFallbackCatalogue(MessageCatalogueContract $catalogue)
    {
        // detect circular references
        $c = $this;

        do {
            if ($c->getLocale() === $catalogue->getLocale()) {
                throw new \LogicException(sprintf('Circular reference detected when adding a fallback catalogue for locale "%s".', $catalogue->getLocale()));
            }
        } while ($c = $c->parent);

        $catalogue->setParent($this);

        $this->fallbackCatalogue = $catalogue;
    }

    /**
     * Set parent.
     *
     * @param MessageCatalogueContract $parent
     *
     * @return self
     */
    public function setParent(MessageCatalogueContract $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getFallbackCatalogue()
    {
        return $this->fallbackCatalogue;
    }
}
