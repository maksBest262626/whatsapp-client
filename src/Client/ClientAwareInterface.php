<?php

declare(strict_types=1);

namespace Maksb\WhatsappClient\Client;

use Maksb\WhatsappClient\Client;

interface ClientAwareInterface
{
    public function setClient(Client $client);
}
