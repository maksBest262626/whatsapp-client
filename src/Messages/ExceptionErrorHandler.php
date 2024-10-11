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
        $responseBody = json_decode(
            $response->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $statusCode = (int)$response->getStatusCode();

        if ($statusCode === 429) {
            throw new ClientException\Exception(
                $responseBody['title'] . ': ' . $responseBody['detail'],
                $response->getStatusCode()
            );
        }

        if ($statusCode >= 500 && $statusCode <= 599) {
            throw new ClientException\Exception($responseBody['title'] . ': ' . $responseBody['detail']);
        }

        throw new ClientException\Exception($responseBody['title'] . ': ' . $responseBody['detail']);
    }
}
