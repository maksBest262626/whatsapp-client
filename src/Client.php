<?php

declare(strict_types=1);

namespace Maksb\WhatsappClient;

use Composer\InstalledVersions;
use Http\Client\HttpClient;
use InvalidArgumentException;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Uri;
use Maksb\WhatsappClient\Client\APIResource;
use Maksb\WhatsappClient\Client\Credentials\Bearer;
use Maksb\WhatsappClient\Client\Credentials\Container;
use Maksb\WhatsappClient\Client\Credentials\CredentialsInterface;
use Maksb\WhatsappClient\Client\Credentials\Handler\BearerHandler;
use Maksb\WhatsappClient\Client\Factory\FactoryInterface;
use Maksb\WhatsappClient\Client\Factory\MapFactory;
use Maksb\WhatsappClient\Logger\LoggerAwareInterface;
use Maksb\WhatsappClient\Logger\LoggerTrait;
use Maksb\WhatsappClient\Messages\ClientFactory as MessagesClientFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;

use function array_key_exists;
use function array_merge;
use function call_user_func_array;
use function http_build_query;
use function implode;
use function is_null;
use function json_encode;
use function method_exists;
use function set_error_handler;
use function str_replace;

/**
 * Whatsapp API Client, allows access to the API from PHP.
 *
 * @property string restUrl
 * @property string apiUrl
 */
class Client implements LoggerAwareInterface
{
    use LoggerTrait;

    public const BASE_API = 'https://graph.facebook.com/v21.0';

    /**
     * API Credentials
     *
     * @var CredentialsInterface
     */
    protected $credentials;

    /**
     * Http Client
     *
     * @var HttpClient
     */
    protected $client;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var ContainerInterface
     */
    protected $factory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $options = ['show_deprecations' => false, 'debug' => false];

    /**
     * @string
     */
    public $apiUrl;

    /**
     * @string
     */
    public $restUrl;

    /**
     * Create a new API client using the provided credentials.
     */
    public function __construct(
        CredentialsInterface $credentials,
        private int $whatsappBusinessPhoneNumberId,
        $options = [],
        ?ClientInterface $client = null
    ) {
        if (is_null($client)) {
            // Since the user did not pass a client, try and make a client
            // using the Guzzle 6 adapter or Guzzle 7 (depending on availability)
            [$guzzleVersion] = explode('@', (string) InstalledVersions::getVersion('guzzlehttp/guzzle'), 1);
            $guzzleVersion = (float) $guzzleVersion;

            if ($guzzleVersion >= 6.0 && $guzzleVersion < 7) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                /** @noinspection PhpUndefinedNamespaceInspection */
                /** @noinspection PhpUndefinedClassInspection */
                $client = new \Http\Adapter\Guzzle6\Client();
            }

            if ($guzzleVersion >= 7.0 && $guzzleVersion < 8.0) {
                $client = new \GuzzleHttp\Client();
            }
        }

        $this->setHttpClient($client);

        if (
            !($credentials instanceof Container) &&
            !($credentials instanceof Bearer)
        ) {
            throw new RuntimeException('unknown credentials type: ' . $credentials::class);
        }

        $this->credentials = $credentials;

        $this->options = array_merge($this->options, $options);

        // If they've provided an app name, validate it
        if (isset($options['app'])) {
            $this->validateAppOptions($options['app']);
        }

        // Set the default URLs. Keep the constants for
        // backwards compatibility
        $this->apiUrl = static::BASE_API;

        // If they've provided alternative URLs, use that instead
        // of the defaults
        if (isset($options['base_api_url'])) {
            $this->apiUrl = $options['base_api_url'];
        }

        if (isset($options['debug'])) {
            $this->debug = $options['debug'];
        }

        $services = [
            // Registered Services by name
            'messages' => MessagesClientFactory::class,

            // Additional utility classes
            APIResource::class => APIResource::class,
            Client::class => fn() => $this
        ];

        $this->setFactory(
            new MapFactory(
                $services,
                $this
            )
        );

        // Disable throwing E_USER_DEPRECATED notices by default, the user can turn it on during development
        if (array_key_exists('show_deprecations', $this->options) && ($this->options['show_deprecations'] == true)) {
            set_error_handler(
                static fn(int $errno, string $errstr, string $errfile = null, int $errline = null, array $errorcontext = null) => true,
                E_USER_DEPRECATED
            );
        }
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function getWhatsappBusinessPhoneNumberId(): int
    {
        return $this->whatsappBusinessPhoneNumberId;
    }

    /**
     * Set the Http Client to used to make API requests.
     *
     * This allows the default http client to be swapped out for a HTTPlug compatible
     * replacement.
     */
    public function setHttpClient(ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the Http Client used to make API requests.
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * Set the factory used to create API specific clients.
     */
    public function setFactory(FactoryInterface $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    public function getFactory(): ContainerInterface
    {
        return $this->factory;
    }


    /**
     * @deprecated Use a configured APIResource with a HandlerInterface
     * Request business logic is being removed from the User Client Layer.
     */
    public static function authRequest(RequestInterface $request, Bearer $credentials): RequestInterface
    {
        $handler = new BearerHandler();

        return $handler($request, $credentials);
    }


    /**
     * @deprecated Use a configured APIResource with a HandlerInterface
     * Request business logic is being removed from the User Client Layer.
     */
    public function get(string $url, array $params = []): ResponseInterface
    {
        $queryString = '?' . http_build_query($params);
        $url .= $queryString;

        $request = new Request($url, 'GET');

        return $this->send($request);
    }

    /**
     * @deprecated Use a configured APIResource with a HandlerInterface
     * Request business logic is being removed from the User Client Layer.
     */
    public function post(string $url, array $params): ResponseInterface
    {
        $request = new Request(
            $url,
            'POST',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($params));

        return $this->send($request);
    }

    /**
     * @deprecated Use a configured APIResource with a HandlerInterface
     * Request business logic is being removed from the User Client Layer.
     */
    public function postUrlEncoded(string $url, array $params): ResponseInterface
    {
        $request = new Request(
            $url,
            'POST',
            'php://temp',
            ['content-type' => 'application/x-www-form-urlencoded']
        );

        $request->getBody()->write(http_build_query($params));

        return $this->send($request);
    }


    /**
     * @deprecated Use a configured APIResource with a HandlerInterface
     * Request business logic is being removed from the User Client Layer.
     */
    public function put(string $url, array $params): ResponseInterface
    {
        $request = new Request(
            $url,
            'PUT',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($params));

        return $this->send($request);
    }

    /**
     * @deprecated Use a configured APIResource with a HandlerInterface
     * Request business logic is being removed from the User Client Layer.
     */
    public function delete(string $url): ResponseInterface
    {
        $request = new Request(
            $url,
            'DELETE'
        );

        return $this->send($request);
    }

    /**
     * Wraps the HTTP Client, creates a new PSR-7 request adding authentication, signatures, etc.
     *
     * @throws ClientExceptionInterface
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        // Allow any part of the URI to be replaced with a simple search
        if (isset($this->options['url'])) {
            foreach ($this->options['url'] as $search => $replace) {
                $uri = (string)$request->getUri();
                $new = str_replace($search, $replace, $uri);

                if ($uri !== $new) {
                    $request = $request->withUri(new Uri($new));
                }
            }
        }

        // The user agent must be in the following format:
        // LIBRARY-NAME/LIBRARY-VERSION LANGUAGE-NAME/LANGUAGE-VERSION [APP-NAME/APP-VERSION]
        // See https://github.com/Vonage/client-library-specification/blob/master/SPECIFICATION.md#reporting
        $userAgent = [];

        // Library name
        $userAgent[] = 'whatsapp-php/' . $this->getVersion();

        // Language name
        $userAgent[] = 'php/' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

        // If we have an app set, add that to the UA
        if (isset($this->options['app'])) {
            $app = $this->options['app'];
            $userAgent[] = $app['name'] . '/' . $app['version'];
        }

        // Set the header. Build by joining all the parts we have with a space
        $request = $request->withHeader('User-Agent', implode(' ', $userAgent));
        $response = $this->client->sendRequest($request);

        if ($this->debug) {
            $id = uniqid('', true);
            $request->getBody()->rewind();
            $response->getBody()->rewind();
            $this->log(
                LogLevel::DEBUG,
                'Request ' . $id,
                [
                    'url' => $request->getUri()->__toString(),
                    'headers' => $request->getHeaders(),
                    'body' => explode("\n", $request->getBody()->__toString())
                ]
            );
            $this->log(
                LogLevel::DEBUG,
                'Response ' . $id,
                [
                    'headers ' => $response->getHeaders(),
                    'body' => explode("\n", $response->getBody()->__toString())
                ]
            );

            $request->getBody()->rewind();
            $response->getBody()->rewind();
        }

        return $response;
    }

    protected function validateAppOptions($app): void
    {
        $disallowedCharacters = ['/', ' ', "\t", "\n"];

        foreach (['name', 'version'] as $key) {
            if (!isset($app[$key])) {
                throw new InvalidArgumentException('app.' . $key . ' has not been set');
            }

            foreach ($disallowedCharacters as $char) {
                if (str_contains((string) $app[$key], $char)) {
                    throw new InvalidArgumentException('app.' . $key . ' cannot contain the ' . $char . ' character');
                }
            }
        }
    }

    public function __call($name, $args)
    {
        if (!$this->factory->has($name)) {
            throw new RuntimeException('no api namespace found: ' . $name);
        }

        $collection = $this->factory->get($name);

        if (empty($args)) {
            return $collection;
        }

        return call_user_func_array($collection, $args);
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($name)
    {
        if (!$this->factory->has($name)) {
            throw new RuntimeException('no api namespace found: ' . $name);
        }

        return $this->factory->get($name);
    }

    protected function getVersion(): string
    {
        return InstalledVersions::getVersion('maksb/whatsapp-client');
    }

    public function getLogger(): ?LoggerInterface
    {
        if (!$this->logger && $this->getFactory()->has(LoggerInterface::class)) {
            $this->setLogger($this->getFactory()->get(LoggerInterface::class));
        }

        return $this->logger;
    }

    public function getCredentials(): CredentialsInterface
    {
        return $this->credentials;
    }

    /**
     * @deprecated Use a configured APIResource with a HandlerInterface
     * Request business logic is being removed from the User Client Layer.
     */
    protected static function requiresBasicAuth(RequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        $isSecretManagementEndpoint = str_starts_with($path, '/accounts') && str_contains($path, '/secrets');
        $isApplicationV2 = str_starts_with($path, '/v2/applications');

        return $isSecretManagementEndpoint || $isApplicationV2;
    }

    /**
     * @deprecated Use a configured APIResource with a HandlerInterface
     * Request business logic is being removed from the User Client Layer.
     */
    protected static function requiresAuthInUrlNotBody(RequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();

        $isRedact =  str_starts_with($path, '/v1/redact');
        $isMessages =  str_starts_with($path, '/v1/messages');

        return $isRedact || $isMessages;
    }

    /**
     * @deprecated Use a configured APIResource with a HandlerInterface
     * Request business logic is being removed from the User Client Layer.
     */
    protected function needsKeypairAuthentication(RequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        $isCallEndpoint = str_starts_with($path, '/v1/calls');
        $isRecordingUrl = str_starts_with($path, '/v1/files');
        $isStitchEndpoint = str_starts_with($path, '/beta/conversation');
        $isUserEndpoint = str_starts_with($path, '/beta/users');

        return $isCallEndpoint || $isRecordingUrl || $isStitchEndpoint || $isUserEndpoint;
    }
}
