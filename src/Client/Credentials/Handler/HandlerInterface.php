<?php

namespace Maksb\WhatsappClient\Client\Credentials\Handler;

use Psr\Http\Message\RequestInterface;
use Maksb\WhatsappClient\Client\Credentials\CredentialsInterface;

interface HandlerInterface
{
    /**
     * Add authentication to a request
     */
    function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface;
}
