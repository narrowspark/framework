<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use Viserio\Component\Parser\Dumper;

class OptionsDumper
{
    /**
     * A dumper instance.
     *
     * @var null|\Viserio\Component\Parser\Dumper
     */
    private $dumper;

    /**
     * Create a new OptionsDumper instance.
     *
     * @param null|\Viserio\Component\Parser\Dumper $dumper
     */
    public function __construct(Dumper $dumper = null)
    {
        $this->dumper = $dumper;
    }

    /**
     * @param string $format
     *
     * @return \Viserio\Component\OptionsResolver\OptionsDumper
     */
    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }
}
