<?php

namespace Maksb\WhatsappClient\Messages\Channel\WhatsApp;

use Maksb\WhatsappClient\Messages\MessageTraits\ContextTrait;
use Maksb\WhatsappClient\Messages\MessageTraits\TextTrait;
use Maksb\WhatsappClient\Messages\Channel\BaseMessage;

class WhatsAppText extends BaseMessage
{
    use ContextTrait;
    use TextTrait;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEXT;
    protected string $channel = 'whatsapp';

    public function __construct(
        string $to,
        string $from,
        string $text
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->text = $text;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['text'] = $this->getText();
        $returnArray['context'] = $this->context ?? null;

        return $returnArray;
    }
}
