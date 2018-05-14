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

namespace Viserio\Contract\Container\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    /**
     * The service id.
     *
     * @var string
     */
    private $id;

    /** @var null|string */
    private $sourceId;

    /** @var array */
    private $alternatives;

    /**
     * Create a new NotFoundException instance.
     *
     * @param string         $id
     * @param null|string    $sourceId
     * @param null|Exception $previous
     * @param array          $alternatives
     * @param null|string    $message
     */
    public function __construct(
        string $id,
        string $sourceId = null,
        Exception $previous = null,
        array $alternatives = [],
        string $message = null
    ) {
        if ($sourceId === null && $message === null) {
            $message = \sprintf('You have requested a non-existent service [%s].', $id);
        } elseif ($message === null) {
            $message = \sprintf('The service [%s] has a dependency on a non-existent service [%s].', $sourceId, $id);
        }

        if (\count($alternatives) !== 0) {
            if (\count($alternatives) === 1) {
                $message .= ' Did you mean this: ["';
            } else {
                $message .= ' Did you mean one of these: ["';
            }

            $message .= \implode('", "', $alternatives) . '"]?';
        }

        parent::__construct($message, 0, $previous);

        $this->id = $id;
        $this->sourceId = $sourceId;
        $this->alternatives = $alternatives;
    }

    /**
     * Get service id.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getSourceId(): ?string
    {
        return $this->sourceId;
    }

    /**
     * @return array
     */
    public function getAlternatives(): array
    {
        return $this->alternatives;
    }
}
