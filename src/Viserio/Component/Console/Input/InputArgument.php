<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Console\Input;

use Symfony\Component\Console\Input\InputArgument as SymfonyInputArgument;

/**
 * Code in this class it taken from silly.
 *
 * See the original here: https://github.com/mnapoli/silly/blob/master/src/Input/InputArgument.php
 *
 * @author Matthieu Napoli https://github.com/mnapoli
 * @copyright Copyright (c) Matthieu Napoli
 */
class InputArgument extends SymfonyInputArgument
{
    /**
     * Input argument description.
     *
     * @var string
     */
    protected $description;

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->description ?: parent::getDescription();
    }

    /**
     * Set the input argument description.
     *
     * @param string $description
     *
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
