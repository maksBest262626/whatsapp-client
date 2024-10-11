<?php

declare(strict_types=1);

namespace Maksb\WhatsappClient\Entity;

trait HasEntityTrait
{
    protected $entity;

    /**
     * @param $entity
     */
    public function setEntity($entity): void
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }
}
