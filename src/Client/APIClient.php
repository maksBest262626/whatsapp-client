<?php
declare(strict_types=1);

namespace Maksb\WhatsappClient\Client;

interface APIClient
{
    public function getAPIResource(): APIResource;
}
