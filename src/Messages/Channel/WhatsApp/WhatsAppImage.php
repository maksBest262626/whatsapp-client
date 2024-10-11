<?php

namespace Maksb\WhatsappClient\Messages\Channel\WhatsApp;

use Maksb\WhatsappClient\Messages\MessageObjects\ImageObject;
use Maksb\WhatsappClient\Messages\Channel\BaseMessage;
use Maksb\WhatsappClient\Messages\MessageTraits\ContextTrait;

class WhatsAppImage extends BaseMessage
{
    use ContextTrait;

    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_IMAGE;

    public function __construct(
        string $to,
        string $from,
        protected ImageObject $image
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['image'] = $this->image->toArray();

        if (!is_null($this->context)) {
            $returnArray['context'] = $this->context;
        }

        return $returnArray;
    }
}
