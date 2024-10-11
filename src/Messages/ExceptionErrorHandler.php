<?php

declare(strict_types=1);

namespace Maksb\WhatsappClient\Messages;

use JsonException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Maksb\WhatsappClient\Client\Exception as ClientException;

use function json_decode;

class ExceptionErrorHandler
{
    /**
     * @throws JsonException
     */
    public function __invoke(ResponseInterface $response, RequestInterface $request)
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 429) {
            throw new ClientException\Exception(
                $response->getBody()->getContents(),
                $response->getStatusCode()
            );
        }

        if ($statusCode >= 500 && $statusCode <= 599) {
            throw new ClientException\Exception($response->getBody()->getContents());
        }

        throw new ClientException\Exception($response->getReasonPhrase().' : '.$response->getBody()->getContents());
    }
}
