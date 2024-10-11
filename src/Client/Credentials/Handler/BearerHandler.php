<?php

namespace Maksb\WhatsappClient\Client\Credentials\Handler;

use Maksb\WhatsappClient\Client\Credentials\Bearer;
use Psr\Http\Message\RequestInterface;
use Maksb\WhatsappClient\Client\Credentials\CredentialsInterface;

class BearerHandler extends AbstractHandler
{
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface
    {
        $credentials = $this->extract(Bearer::class, $credentials);

        $c = $credentials->asArray();

        $request = $request->withHeader('Authorization', 'Bearer ' . $c['bearer']);

        return $request;
    }
}