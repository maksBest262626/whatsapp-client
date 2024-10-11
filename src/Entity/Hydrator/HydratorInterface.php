<?php

declare(strict_types=1);

namespace Maksb\WhatsappClient\Entity\Hydrator;

interface HydratorInterface
{
    /**
     * Hydrate an object that the hydrator creates
     */
    public function hydrate(array $data);

    /**
     * Hydrate an existing object created outside of the hydrator
     *
     * @param $object
     */
    public function hydrateObject(array $data, $object);
}
