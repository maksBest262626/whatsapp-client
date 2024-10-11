<?php

namespace Maksb\WhatsappClient\Messages\Channel\WhatsApp;

use Maksb\WhatsappClient\Messages\MessageObjects\AudioObject;
use Maksb\WhatsappClient\Messages\Channel\BaseMessage;
use Maksb\WhatsappClient\Messages\MessageTraits\ContextTrait;

class WhatsAppAudio extends BaseMessage
{
    use ContextTrait;

    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_AUDIO;

    public function __construct(
        string $to,
        string $from,
        protected AudioObject $audioObject
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['audio'] = $this->audioObject->toArray();

        if (!is_null($this->context)) {
            $returnArray['context'] = $this->context;
        }

        return $returnArray;
    }
}
