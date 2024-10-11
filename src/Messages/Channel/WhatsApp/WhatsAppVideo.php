<?php

namespace Maksb\WhatsappClient\Messages\Channel\WhatsApp;

use Maksb\WhatsappClient\Messages\MessageObjects\VideoObject;
use Maksb\WhatsappClient\Messages\Channel\BaseMessage;
use Maksb\WhatsappClient\Messages\MessageTraits\ContextTrait;

class WhatsAppVideo extends BaseMessage
{
    use ContextTrait;

    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_VIDEO;

    public function __construct(
        string $to,
        string $from,
        protected VideoObject $videoObject
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['video'] = $this->videoObject->toArray();

        if (!is_null($this->context)) {
            $returnArray['context'] = $this->context;
        }

        return $returnArray;
    }
}