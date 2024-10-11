<?php

declare(strict_types=1);

namespace Maksb\WhatsappClient\Entity\Filter;

class EmptyFilter implements FilterInterface
{

    public function getQuery(): array
    {
        return [];
    }
}
