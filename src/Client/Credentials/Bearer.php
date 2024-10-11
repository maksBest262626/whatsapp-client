<?php

declare(strict_types=1);

namespace Maksb\WhatsappClient\Client\Credentials;

/**
 * Class Basic
 * Read-only container for api key and secret.
 *
 * @property string api_key
 * @property string api_secret
 */
class Bearer extends AbstractCredentials
{
    /**
     * Create a credential set with an API key and secret.
     *
     * @param $bearer
     */
    public function __construct($bearer)
    {
        $this->credentials['bearer'] = (string)$bearer;
    }
}
