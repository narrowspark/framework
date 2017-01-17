<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Routing;

interface Pattern
{
    public const ANY = '.+';

    public const ALPHA = '[a-zA-Z]+';

    public const ALPHA_NUM = '[a-zA-Z\d]+';

    public const ALPHA_NUM_DASH = '[a-zA-Z\d\-]+';

    public const ALPHA_UPPER = '[A-Z]+';

    public const ALPHA_LOWER = '[a-z]+';

    public const DIGITS = '\d+';

    public const NUMBER = '[0-9]+';

    public const WORD = '[a-zA-Z]+';

    public const SLUG = '[a-z0-9-]+';

    public const UUID = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+';
}
