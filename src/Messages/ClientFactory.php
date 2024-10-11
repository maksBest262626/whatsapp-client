<?php

namespace Maksb\WhatsappClient\Messages;

use Maksb\WhatsappClient\Client\Credentials\Handler\BearerHandler;
use Psr\Container\ContainerInterface;
use Maksb\WhatsappClient\Client\APIResource;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUrl($api->getClient()->getApiUrl() . '/v1/messages')
            ->setIsHAL(false)
            ->setErrorsOn200(false)
            ->setAuthHandlers([new BearerHandler()])
            ->setExceptionErrorHandler(new ExceptionErrorHandler());

        return new Client($api);
    }
}