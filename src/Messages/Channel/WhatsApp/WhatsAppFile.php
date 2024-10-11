<?php

namespace Maksb\WhatsappClient\Messages\Channel\WhatsApp;

use Maksb\WhatsappClient\Messages\MessageObjects\FileObject;
use Maksb\WhatsappClient\Messages\Channel\BaseMessage;
use Maksb\WhatsappClient\Messages\MessageTraits\ContextTrait;

class WhatsAppFile extends BaseMessage
{
    use ContextTrait;

    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_FILE;

    public function __construct(
        string $to,
        string $from,
        protected FileObject $fileObject
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['file'] = $this->fileObject->toArray();

        if (!is_null($this->context)) {
            $returnArray['context'] = $this->context;
        }

        return $returnArray;
    }
}