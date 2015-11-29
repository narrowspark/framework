<?php
namespace Viserio\Contracts\Translator;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0
 */

/**
 * MessageCatalogue.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
interface MessageCatalogue
{
    /**
     * Gets the catalogue locale.
     *
     * @return string The locale
     */
    public function getLocale();

    /**
     * Gets the domains.
     *
     * @return array An array of domains
     */
    public function getDomains();

    /**
     * Gets the messages within a given domain.
     *
     * If $domain is null, it returns all messages.
     *
     * @param string|null $domain The domain name
     *
     * @return array An array of messages
     */
    public function all($domain = null);

    /**
     * Sets a message translation.
     *
     * @param string $id          The message id
     * @param string $translation The messages translation
     * @param string $domain      The domain name
     */
    public function set($id, $translation, $domain = 'messages');

    /**
     * Checks if a message has a translation.
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return bool true if the message has a translation, false otherwise
     */
    public function has($id, $domain = 'messages');

    /**
     * Checks if a message has a translation (it does not take into account the fallback mechanism).
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return bool true if the message has a translation, false otherwise
     */
    public function defines($id, $domain = 'messages');

    /**
     * Gets a message translation.
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return string The message translation
     */
    public function get($id, $domain = 'messages');

    /**
     * Sets translations for a given domain.
     *
     * @param array  $messages An array of translations
     * @param string $domain   The domain name
     */
    public function replace($messages, $domain = 'messages');

    /**
     * Adds translations for a given domain.
     *
     * @param array  $messages An array of translations
     * @param string $domain   The domain name
     */
    public function add($messages, $domain = 'messages');

    /**
     * Merges translations from the given Catalogue into the current one.
     *
     * The two catalogues must have the same locale.
     *
     * @param MessageCatalogue $catalogue A MessageCatalogue instance
     */
    public function addCatalogue(MessageCatalogue $catalogue);

    /**
     * Merges translations from the given Catalogue into the current one
     * only when the translation does not exist.
     *
     * This is used to provide default translations when they do not exist for the current locale.
     *
     * @param MessageCatalogue $catalogue A MessageCatalogue instance
     */
    public function addFallbackCatalogue(MessageCatalogue $catalogue);

    /**
     * Gets the fallback catalogue.
     *
     * @return MessageCatalogue|null A MessageCatalogue instance or null when no fallback has been set
     */
    public function getFallbackCatalogue();
}
