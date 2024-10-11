<?php

namespace Maksb\WhatsappClient\Messages\Channel\WhatsApp;

use Maksb\WhatsappClient\Messages\MessageObjects\FileObject;
use Maksb\WhatsappClient\Messages\MessageObjects\TemplateObject;
use Maksb\WhatsappClient\Messages\Channel\BaseMessage;
use Maksb\WhatsappClient\Messages\MessageTraits\ContextTrait;

class WhatsAppTemplate extends BaseMessage
{
    use ContextTrait;

    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEMPLATE;

    public function __construct(
        string $to,
        string $from,
        protected TemplateObject $templateObject,
        protected string $locale
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function toArray(): array
    {
        $returnArray = [
            'template' => $this->templateObject->toArray(),
            'whatsapp' => [
                'policy' => 'deterministic',
                'locale' => $this->getLocale()
            ]
        ];

        if (!is_null($this->context)) {
            $returnArray['context'] = $this->context;
        }

        return array_merge($this->getBaseMessageUniversalOutputArray(), $returnArray);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }
}