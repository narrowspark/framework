<?php
declare(strict_types=1);
namespace Viserio\Routing\Matchers;

class ParameterMatcher
{
    /**
     * @var string[]
     */
    protected $names;

    /**
     * @var string
     */
    protected $regex;

    /**
     * Create a new any matcher instance.
     *
     * @param array|string $names
     * @param string       $regex
     */
    public function __construct($names, string $regex)
    {
        $this->names = is_array($names) ? $names : [$names];
        $this->regex = $regex;
    }
}
