<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFunction;
use Viserio\Component\Contracts\Session\Store as StoreContract;

class SessionExtension extends Twig_Extension
{
    /**
     * Viserio session instance.
     *
     * @var \Viserio\Component\Contracts\Session\Store
     */
    protected $session;

    /**
     * Create a new session extension.
     *
     * @param \Viserio\Component\Contracts\Session\Store $session
     */
    public function __construct(StoreContract $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Viserio_Bridge_Twig_Extension_Session';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new Twig_SimpleFunction('session', [$this->session, 'get']),
            new Twig_SimpleFunction('csrf_token', [$this->session, 'getToken'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('csrf_field', 'csrf_field', ['is_safe' => ['html']]),
            new Twig_SimpleFunction('method_field', 'method_field', ['is_safe' => ['html']]),
            new Twig_SimpleFunction('session_get', [$this->session, 'get']),
            new Twig_SimpleFunction('session_pull', [$this->session, 'pull']),
            new Twig_SimpleFunction('session_has', [$this->session, 'has']),
        ];
    }
}
