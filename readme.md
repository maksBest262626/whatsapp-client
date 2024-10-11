# Getting started.
For install:

    composer require maksb/whatsapp-client

For symfony projects You need to add this to services.yaml

    Maksb\WhatsappClient\Client\Credentials\CredentialsInterface:
        class: 'Maksb\WhatsappClient\Client\Credentials\Bearer'
        arguments:
            $bearer: '%env(string:WHATSAPP_BEARER_TOKEN)%'

    Maksb\WhatsappClient\Client:
        arguments:
            $whatsappBusinessPhoneNumberId: '%env(int:WHATSAPP_BUSINESS_PHONE_NUMBER_ID)%'

Basic usage is:

    use Maksb\WhatsappClient\Client;
    use Maksb\WhatsappClient\Messages\Channel\WhatsApp\WhatsAppTemplate;
    use Maksb\WhatsappClient\Messages\MessageObjects\TemplateObject;

    $toNumber = '447123456789';
    $locale = 'en_US';
    $templateName = 'abc_123:sample_issue_resolution';
    $templateParams = [
        ['type' => 'text', 'text' => 'Hello World!'],
        ['type' => 'currency', 'currency' => ['fallback_value' => 'USD', 'code' => 'USD', 'amount_1000' => 100]],
        ['type' => 'date_time', 'date_time' => ['fallback_value' => (new \DateTime())->format('Y-m-d H:i:s')]],
        ['type' => 'location', 'location' => ['latitude' => '37.483307', 'longitude' => '122.148981', 'name' => 'Pablo Morales', 'address' => '1 Hacker Way, Menlo Park, CA 94025']],
    ];

    $whatsAppMessage = new WhatsAppTemplate(
        to: $toNumber,
        templateObject: new TemplateObject(
            name: $templateName,
            parameters: $templateParams,
            language: $locale,
        ),
        locale: $locale,
    );

    $whatsAppMessagesClient = $client->getFactory()->get('messages');
    /** @var $whatsAppMessagesClient \Maksb\WhatsappClient\Messages\Client */
    $whatsAppMessagesClient->send($whatsAppMessage);