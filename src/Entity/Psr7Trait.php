<?php

declare(strict_types=1);

namespace Maksb\WhatsappClient\Entity;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Maksb\WhatsappClient\Entity\Hydrator\ArrayHydrateInterface;

use function array_merge;
use function get_class;
use function is_array;
use function json_decode;
use function method_exists;
use function parse_str;
use function trigger_error;

/**
 * Class Psr7Trait
 *
 * Allow an entity to contain last request / response objects.
 */
trait Psr7Trait
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @deprecated See error
     *
     * @param ResponseInterface $response
     *
     * @return void
     * @throws \Exception
     */
    public function setResponse(ResponseInterface $response): void
    {
        trigger_error(
            $this::class . '::setResponse() is deprecated and will be removed',
            E_USER_DEPRECATED
        );

        $this->response = $response;
        $status = (int)$response->getStatusCode();

        if ($this instanceof ArrayHydrateInterface && (200 === $status || 201 === $status)) {
            $this->fromArray($this->getResponseData());
        }
    }

    public function setRequest(RequestInterface $request): void
    {
        trigger_error(
            $this::class . '::setRequest is deprecated and will be removed',
            E_USER_DEPRECATED
        );

        $this->request = $request;
        $this->data = [];

        if (method_exists($request, 'getQueryParams')) {
            $this->data = $request->getQueryParams();
        }

        $contentType = $request->getHeader('Content-Type');

        if (!empty($contentType)) {
            if ($contentType[0] === 'application/json') {
                $body = json_decode($request->getBody()->getContents(), true);
                if (is_array($body)) {
                    $this->data = array_merge(
                        $this->data,
                        $body
                    );
                }
            }
        } else {
            parse_str($request->getBody()->getContents(), $body);
            $this->data = array_merge($this->data, $body);
        }
    }

    /**
     * @deprecated See error
     * @return RequestInterface|null
     */
    public function getRequest(): ?RequestInterface
    {
        trigger_error(
            $this::class . '::getRequest() is deprecated. ' .
            'Please get the APIResource from the appropriate client to get this information',
            E_USER_DEPRECATED
        );

        return $this->request;
    }

    /**
     * @deprecated See error
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        trigger_error(
            $this::class . '::getResponse() is deprecated. ' .
            'Please get the APIResource from the appropriate client to get this information',
            E_USER_DEPRECATED
        );

        return $this->response;
    }
}
