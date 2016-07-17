<?php
namespace Viserio\Contracts\Translation;

interface MessageCatalogue
{
    /**
     * Gets the catalogue locale.
     *
     * @return string The locale
     */
    public function getLocale(): string;

    /**
     * Gets the domains.
     *
     * @return array An array of domains
     */
    public function getDomains(): array;

    /**
     * Gets the messages within a given domain.
     *
     * If $domain is null, it returns all messages.
     *
     * @param string|null $domain The domain name
     *
     * @return array An array of messages
     */
    public function all(string $domain = null): array;

    /**
     * Sets a message translation.
     *
     * @param string $id          The message id
     * @param string $translation The messages translation
     * @param string $domain      The domain name
     *
     * @return void
     */
    public function set(string $id, string $translation, string $domain = 'messages');

    /**
     * Checks if a message has a translation.
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return bool true if the message has a translation, false otherwise
     */
    public function has(string $id, string $domain = 'messages'): bool;

    /**
     * Checks if a message has a translation (it does not take into account the fallback mechanism).
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return bool true if the message has a translation, false otherwise
     */
    public function defines(string $id, string $domain = 'messages'): bool;

    /**
     * Gets a message translation.
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return string The message translation
     */
    public function get(string $id, string $domain = 'messages'): string;

    /**
     * Sets translations for a given domain.
     *
     * @param array  $messages An array of translations
     * @param string $domain   The domain name
     *
     * @return void
     */
    public function replace(array $messages, string $domain = 'messages');

    /**
     * Removes a record.
     *
     * @param string $messages
     * @param string $domain
     *
     * @return void
     */
    public function remove(string $messages, string $domain = 'messages');

    /**
     * Adds translations for a given domain.
     *
     * @param array  $messages An array of translations
     * @param string $domain   The domain name
     *
     * @return void
     */
    public function add(array $messages, string $domain = 'messages');

    /**
     * Merges translations from the given Catalogue into the current one.
     *
     * The two catalogues must have the same locale.
     *
     * @param MessageCatalogue $catalogue A MessageCatalogue instance
     *
     * @throws \LogicException
     *
     * @return void
     */
    public function addCatalogue(MessageCatalogue $catalogue);

    /**
     * Merges translations from the given Catalogue into the current one
     * only when the translation does not exist.
     *
     * This is used to provide default translations when they do not exist for the current locale.
     *
     * @param MessageCatalogue $catalogue A MessageCatalogue instance
     *
     * @throws \LogicException
     *
     * @return void
     */
    public function addFallbackCatalogue(MessageCatalogue $catalogue);

    /**
     * Gets the fallback catalogue.
     *
     * @return MessageCatalogue|null A MessageCatalogue instance or null when no fallback has been set
     */
    public function getFallbackCatalogue();

    /**
     * Set parent.
     *
     * @param MessageCatalogue $parent
     *
     * @return $this
     */
    public function setParent(MessageCatalogue $parent): MessageCatalogue;
}
